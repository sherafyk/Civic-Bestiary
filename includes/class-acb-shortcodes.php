<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Shortcodes
{
    private const COOKIE_NAME = 'acb_profile_token';

    private $repository;
    private $scoring_engine;

    public function __construct(ACB_Repository $repository, ACB_Scoring_Engine $scoring_engine)
    {
        $this->repository = $repository;
        $this->scoring_engine = $scoring_engine;
    }

    public function register_hooks()
    {
        add_action('init', array($this, 'ensure_cookie'), 1);
        add_shortcode('american_civic_bestiary', array($this, 'render_assessment'));
        add_shortcode('civic_bestiary', array($this, 'render_assessment'));
        add_shortcode('acb_dashboard', array($this, 'render_dashboard_shortcode'));
    }

    public function ensure_cookie()
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            return;
        }

        $this->set_profile_cookie(wp_generate_password(40, false, false));
    }

    public function render_assessment($atts = array())
    {
        $atts = shortcode_atts(array(
            'limit' => 0,
            'show_dashboard' => 'yes',
        ), $atts, 'american_civic_bestiary');

        $settings = $this->repository->settings();
        ACB_Assets::enqueue_public($settings);

        $limit = absint($atts['limit']);
        if ($limit <= 0) {
            $limit = (int) $settings['questions_per_session'];
        }

        $profile = $this->current_profile();
        $notice = '';

        if ($this->is_submission()) {
            if (!$this->rate_limit_ok($profile)) {
                $notice = $this->notice(__('Too many submissions. Please wait a bit before trying again.', 'american-civic-bestiary'), 'error');
            } else {
                $notice = $this->handle_submission($profile);
                $profile = $this->repository->get_profile((int) $profile['id']);
            }
        }

        if (empty($profile['latest_result']) && (int) $profile['total_answered'] > 0) {
            $profile = $this->score_and_store($profile);
        }

        $result = $profile['latest_result'] ?? array();
        $minimum_met = !empty($result['meta']['minimum_met']);
        $remaining_to_minimum = max(0, (int) ($settings['minimum_questions'] ?? 10) - (int) ($profile['total_answered'] ?? 0));
        $questions = $this->repository->get_active_questions($limit, (int) $profile['id']);
        $has_unanswered = !empty($questions);
        $show_dashboard = 'yes' === strtolower((string) $atts['show_dashboard']) && !empty($profile['latest_result']) && $minimum_met;

        $dashboard_html = $show_dashboard ? $this->render_dashboard($profile, $settings) : '';
        $form_html = '';
        $empty_message = '';
        $progress_html = '';
        $refine_intro_html = '';
        $refine_mode = $minimum_met ? ($settings['refine_display'] ?? 'button') : 'automatic';

        if (!$minimum_met) {
            $progress_html = ACB_Template::render('progress.php', array(
                'answered' => (int) ($profile['total_answered'] ?? 0),
                'minimum' => (int) ($settings['minimum_questions'] ?? 10),
                'remaining' => $remaining_to_minimum,
            ));
        }

        if ($questions) {
            $form_html = $this->render_form($profile, $questions, $settings, $minimum_met ? 'refine' : 'initial');
            if ($minimum_met) {
                $refine_intro_html = ACB_Template::render('refine-panel.php', array(
                    'form_html' => $form_html,
                    'refine_mode' => $refine_mode,
                    'question_count' => count($questions),
                ));
                $form_html = '';
            }
        } elseif ((int) $this->repository->count_questions(true) > 0) {
            $empty_message = $minimum_met
                ? __('You have answered every active Bestiary question currently available. Your profile is complete for now.', 'american-civic-bestiary')
                : __('There are no unanswered active questions available for this profile yet.', 'american-civic-bestiary');
        } else {
            $empty_message = __('No Bestiary questions are active yet.', 'american-civic-bestiary');
        }

        return ACB_Template::render('assessment.php', array(
            'shell_attributes' => ACB_Assets::wrapper_attributes($settings, 'assessment'),
            'notice_html' => $notice,
            'dashboard_html' => $dashboard_html,
            'form_html' => $form_html,
            'progress_html' => $progress_html,
            'refine_intro_html' => $refine_intro_html,
            'empty_message' => $empty_message,
            'minimum_met' => $minimum_met,
            'has_unanswered' => $has_unanswered,
            'report_position' => $settings['report_position'] ?? 'before_form',
        ));
    }

    public function render_dashboard_shortcode($atts = array())
    {
        $settings = $this->repository->settings();
        ACB_Assets::enqueue_public($settings);

        $profile = $this->current_profile();
        if (empty($profile['latest_result']) && (int) $profile['total_answered'] > 0) {
            $profile = $this->score_and_store($profile);
        }

        return ACB_Template::render('dashboard-shortcode.php', array(
            'shell_attributes' => ACB_Assets::wrapper_attributes($settings, 'dashboard'),
            'dashboard_html' => $this->render_dashboard($profile, $settings),
        ));
    }

    private function handle_submission(array $profile)
    {
        if (!isset($_POST['_acb_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_acb_nonce'])), 'acb_submit_questions')) {
            return $this->notice(__('Your session expired. Please try again.', 'american-civic-bestiary'), 'error');
        }

        $settings = $this->repository->settings();
        if (!empty($settings['consent_text']) && empty($_POST['acb_consent'])) {
            return $this->notice(__('Please confirm the educational-use consent before continuing.', 'american-civic-bestiary'), 'error');
        }

        $name = sanitize_text_field(wp_unslash($_POST['acb_name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['acb_email'] ?? ''));
        $this->repository->update_profile_identity((int) $profile['id'], $name, $email);

        $question_ids = isset($_POST['acb_question_ids']) && is_array($_POST['acb_question_ids'])
            ? array_map('absint', wp_unslash($_POST['acb_question_ids']))
            : array();
        $posted = isset($_POST['acb_answers']) && is_array($_POST['acb_answers'])
            ? wp_unslash($_POST['acb_answers'])
            : array();

        $errors = array();
        $prepared = array();
        $already_answered = $this->repository->answered_question_ids((int) $profile['id']);
        foreach ($question_ids as $question_id) {
            $question = $this->repository->get_question($question_id);
            if (!$question || empty($question['active'])) {
                continue;
            }

            if (empty($settings['allow_retakes']) && in_array((int) $question_id, $already_answered, true)) {
                continue;
            }

            $raw_answer = $posted[$question_id] ?? null;
            if ($this->missing_answer($question, $raw_answer)) {
                if (!empty($question['required'])) {
                    $errors[] = sprintf(__('Please answer: %s', 'american-civic-bestiary'), wp_strip_all_tags($question['prompt']));
                }
                continue;
            }

            $delta = $this->repository->build_answer_delta($question, $raw_answer);
            if (empty($delta['answer']['selected'])) {
                $errors[] = sprintf(__('Invalid answer for: %s', 'american-civic-bestiary'), wp_strip_all_tags($question['prompt']));
                continue;
            }
            if (!$this->answer_count_ok($question, $delta['answer'])) {
                $errors[] = sprintf(__('Please check the number of selections for: %s', 'american-civic-bestiary'), wp_strip_all_tags($question['prompt']));
                continue;
            }

            $prepared[] = array(
                'question' => $question,
                'delta' => $delta,
            );
        }

        if ($errors) {
            return $this->notice(implode(' ', $errors), 'error');
        }

        if (!$prepared) {
            return $this->notice(__('No new answers were saved. Already answered questions are skipped to keep your profile history stable.', 'american-civic-bestiary'), 'error');
        }

        foreach ($prepared as $row) {
            $this->repository->save_answer((int) $profile['id'], $row['question'], $row['delta']);
        }

        $profile = $this->score_and_store($profile);
        $result = $profile['latest_result'];
        $minimum_met = !empty($result['meta']['minimum_met']);

        if (!$minimum_met) {
            $remaining = max(0, (int) $settings['minimum_questions'] - (int) $result['meta']['total_answered']);
            return $this->notice(sprintf(_n('%d more answer will unlock the full animal profile.', '%d more answers will unlock the full animal profile.', $remaining, 'american-civic-bestiary'), $remaining), 'success');
        }

        return $this->notice(__('Your Bestiary profile has been updated.', 'american-civic-bestiary'), 'success');
    }

    private function render_form(array $profile, array $questions, array $settings, $flow_stage = 'initial')
    {
        $question_html = '';
        foreach ($questions as $index => $question) {
            $question_html .= $this->render_question($question, $index + 1);
        }

        return ACB_Template::render('form.php', array(
            'profile' => $profile,
            'questions' => $questions,
            'settings' => $settings,
            'question_html' => $question_html,
            'flow_stage' => sanitize_key($flow_stage),
        ));
    }

    private function render_question(array $question, $number)
    {
        return ACB_Template::render('question.php', array(
            'question' => $question,
            'number' => (int) $number,
            'options_html' => $this->question_options_html($question),
        ));
    }

    private function question_options_html(array $question)
    {
        $type = $question['question_type'] ?? 'single_choice';
        $options_html = '';

        foreach ((array) ($question['options'] ?? array()) as $option) {
            $options_html .= $this->render_option($question, $option);
        }

        if ('ranked_choice' === $type) {
            $options_html .= ACB_Template::render('components/ranked-choice-help.php', array(
                'count' => count((array) ($question['options'] ?? array())),
                'select_options_html' => $this->rank_options_html(count((array) ($question['options'] ?? array()))),
            ));
        }

        return $options_html;
    }

    private function render_option(array $question, array $option)
    {
        $type = $question['question_type'] ?? 'single_choice';
        $input_name = 'acb_answers[' . (int) $question['id'] . ']';

        if ('multi_choice' === $type) {
            return sprintf(
                '<label class="acb-option"><input type="checkbox" name="%1$s[]" value="%2$s"><span>%3$s</span></label>',
                esc_attr($input_name),
                esc_attr($option['option_key']),
                esc_html($option['label'])
            );
        }

        if ('ranked_choice' === $type) {
            return sprintf(
                '<label class="acb-option acb-option--rank"><span>%1$s</span><select name="%2$s[%3$s]"><option value="">%4$s</option>%5$s</select></label>',
                esc_html($option['label']),
                esc_attr($input_name),
                esc_attr($option['option_key']),
                esc_html__('No rank', 'american-civic-bestiary'),
                $this->rank_options_html(count((array) ($question['options'] ?? array())))
            );
        }

        $required = !empty($question['required']) ? ' required' : '';

        return sprintf(
            '<label class="acb-option"><input type="radio" name="%1$s" value="%2$s"%4$s><span>%3$s</span></label>',
            esc_attr($input_name),
            esc_attr($option['option_key']),
            esc_html($option['label']),
            $required
        );
    }

    private function rank_options_html($count)
    {
        $html = '';
        for ($i = 1; $i <= $count; $i++) {
            $html .= '<option value="' . esc_attr($i) . '">' . esc_html($i) . '</option>';
        }

        return $html;
    }

    private function render_dashboard(array $profile, array $settings)
    {
        $result = $profile['latest_result'] ?? array();
        if (!$result) {
            return ACB_Template::render('empty-dashboard.php', array(
                'message' => __('Your civic profile is ready to begin.', 'american-civic-bestiary'),
            ));
        }

        $minimum_met = !empty($result['meta']['minimum_met']);
        $primary = $result['animals']['primary'] ?? array();
        $secondary = $result['animals']['secondary'] ?? array();
        $house = $result['house'] ?? array();
        $blend = $result['animals']['blend_type'] ?? array();
        $title = $minimum_met
            ? trim(($primary['label'] ?? __('Unknown', 'american-civic-bestiary')) . ' · ' . ($house['label'] ?? ''), ' ·')
            : __('Profile Calibrating', 'american-civic-bestiary');

        $top_matches = array_slice((array) ($result['animals']['matches'] ?? array()), 0, max(1, (int) ($settings['top_match_count'] ?? 8)));

        return ACB_Template::render('dashboard.php', array(
            'profile' => $profile,
            'result' => $result,
            'settings' => $settings,
            'minimum_met' => $minimum_met,
            'title' => $title,
            'primary' => $primary,
            'secondary' => $secondary,
            'house' => $house,
            'blend' => $blend,
            'primary_icon' => ACB_Assets::animal_icon_url($primary['key'] ?? ''),
            'secondary_icon' => ACB_Assets::animal_icon_url($secondary['key'] ?? ''),
            'dimension_bars' => $this->bars($result['dimensions'] ?? array()),
            'animal_bars' => $this->animal_bars($top_matches, !empty($settings['show_icons'])),
            'house_bars' => $this->house_bars($result['house']['matches'] ?? array()),
            'capture_bars' => $this->bars($result['updown']['subfactors'] ?? array()),
            'calibration_copy' => $this->calibration_copy($result),
            'cta_url' => esc_url($settings['cta_url'] ?? ''),
            'cta_label' => sanitize_text_field($settings['cta_label'] ?? ''),
            'dashboard_outro' => sanitize_textarea_field($settings['dashboard_outro'] ?? ''),
        ));
    }

    private function bars(array $rows)
    {
        return ACB_Template::render('components/bar-list.php', array(
            'rows' => $rows,
            'metric_key' => 'score',
            'label_callback' => function ($row) {
                return $row['label'] ?? '';
            },
        ));
    }

    private function animal_bars(array $rows, $show_icons = true)
    {
        return ACB_Template::render('components/bar-list.php', array(
            'rows' => $rows,
            'metric_key' => 'similarity',
            'label_callback' => function ($row) {
                return trim(($row['label'] ?? '') . ' · ' . ($row['title'] ?? ''));
            },
            'show_icons' => !empty($show_icons),
        ));
    }

    private function house_bars(array $rows)
    {
        return ACB_Template::render('components/bar-list.php', array(
            'rows' => $rows,
            'metric_key' => 'score',
            'label_callback' => function ($row) {
                return $row['label'] ?? '';
            },
            'compact' => true,
        ));
    }

    private function calibration_copy(array $result)
    {
        $minimum = (int) ($result['meta']['minimum_questions'] ?? 10);
        $answered = (int) ($result['meta']['total_answered'] ?? 0);
        $remaining = max(0, $minimum - $answered);

        return sprintf(_n('Answer %d more scenario to unlock the full animal profile.', 'Answer %d more scenarios to unlock the full animal profile.', $remaining, 'american-civic-bestiary'), $remaining);
    }

    private function set_profile_cookie($token)
    {
        $token = sanitize_text_field($token);
        $_COOKIE[self::COOKIE_NAME] = $token;

        if (headers_sent()) {
            return;
        }

        $expires = time() + YEAR_IN_SECONDS;
        if (PHP_VERSION_ID >= 70300) {
            setcookie(self::COOKIE_NAME, $token, array(
                'expires' => $expires,
                'path' => COOKIEPATH ?: '/',
                'domain' => COOKIE_DOMAIN,
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ));
            return;
        }

        setcookie(self::COOKIE_NAME, $token, $expires, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    }

    private function current_profile()
    {
        $token = sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME] ?? ''));
        if (!$token) {
            $token = wp_generate_password(40, false, false);
            $this->set_profile_cookie($token);
        }

        $profile_key = $this->repository->profile_key_from_token($token);
        $user_id = get_current_user_id();

        return $this->repository->find_or_create_profile($profile_key, $user_id);
    }

    private function score_and_store(array $profile)
    {
        $answers = $this->repository->answers_for_profile((int) $profile['id']);
        $result = $this->scoring_engine->score($answers, $this->repository->count_questions(true), $this->repository->settings());
        $this->repository->save_profile_result((int) $profile['id'], $result);

        return $this->repository->get_profile((int) $profile['id']);
    }

    private function is_submission()
    {
        return isset($_POST['acb_action']) && 'submit_questions' === sanitize_key(wp_unslash($_POST['acb_action']));
    }

    private function missing_answer(array $question, $answer)
    {
        if ('ranked_choice' === ($question['question_type'] ?? '') && is_array($answer)) {
            return !array_filter(array_map('absint', $answer));
        }

        if (is_array($answer)) {
            return empty(array_filter($answer));
        }

        return null === $answer || '' === trim((string) $answer);
    }

    private function answer_count_ok(array $question, array $answer)
    {
        $selected = $answer['selected'] ?? array();
        $selected_count = count((array) $selected);
        $min = !empty($question['required']) ? max(1, (int) ($question['min_answers'] ?? 1)) : max(0, (int) ($question['min_answers'] ?? 0));
        $max = max($min, (int) ($question['max_answers'] ?? 1));

        if ($selected_count < $min || $selected_count > $max) {
            return false;
        }

        if ('ranked_choice' === ($question['question_type'] ?? '') && !empty($answer['ranks']) && count($answer['ranks']) !== count(array_unique($answer['ranks']))) {
            return false;
        }

        return true;
    }

    private function notice($message, $type)
    {
        return '<div class="acb-notice acb-notice--' . esc_attr($type) . '">' . esc_html($message) . '</div>';
    }

    private function rate_limit_ok(array $profile)
    {
        $key = 'acb_rate_' . (int) ($profile['id'] ?? 0);
        $count = (int) get_transient($key);
        if ($count >= 20) {
            return false;
        }

        set_transient($key, $count + 1, HOUR_IN_SECONDS);
        return true;
    }
}
