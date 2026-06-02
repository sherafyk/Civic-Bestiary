<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Repository
{
    public function settings()
    {
        $settings = get_option('acb_settings', array());
        return wp_parse_args(is_array($settings) ? $settings : array(), ACB_Core_Data::settings_defaults());
    }

    public function update_settings(array $settings)
    {
        $defaults = ACB_Core_Data::settings_defaults();
        $shadow = sanitize_key($settings['panel_shadow'] ?? $defaults['panel_shadow']);
        if (!in_array($shadow, array('none', 'small', 'medium', 'large'), true)) {
            $shadow = $defaults['panel_shadow'];
        }

        $top_match_count = max(1, min(16, absint($settings['top_match_count'] ?? $defaults['top_match_count'])));
        $layout_mode = sanitize_key($settings['report_layout_mode'] ?? $defaults['report_layout_mode']);
        if (!in_array($layout_mode, array('auto', 'single', 'two', 'three'), true)) {
            $layout_mode = $defaults['report_layout_mode'];
        }
        $report_position = sanitize_key($settings['report_position'] ?? $defaults['report_position']);
        if (!in_array($report_position, array('before_form', 'after_form'), true)) {
            $report_position = $defaults['report_position'];
        }
        $refine_display = sanitize_key($settings['refine_display'] ?? $defaults['refine_display']);
        if (!in_array($refine_display, array('button', 'automatic'), true)) {
            $refine_display = $defaults['refine_display'];
        }
        $report_max_width = trim((string) ($settings['report_max_width'] ?? $defaults['report_max_width']));
        if ('' !== $report_max_width && !preg_match('/^\d+(px|rem|em|%)$/', $report_max_width)) {
            $report_max_width = $defaults['report_max_width'];
        }
        $clean = array(
            'minimum_questions' => max(1, absint($settings['minimum_questions'] ?? $defaults['minimum_questions'])),
            'questions_per_session' => max(1, absint($settings['questions_per_session'] ?? $defaults['questions_per_session'])),
            'show_email_field' => !empty($settings['show_email_field']),
            'show_name_field' => !empty($settings['show_name_field']),
            'consent_text' => wp_kses_post($settings['consent_text'] ?? $defaults['consent_text']),
            'retain_anonymous_days' => absint($settings['retain_anonymous_days'] ?? 0),
            'assessment_eyebrow' => sanitize_text_field($settings['assessment_eyebrow'] ?? $defaults['assessment_eyebrow']),
            'assessment_title' => sanitize_text_field($settings['assessment_title'] ?? $defaults['assessment_title']),
            'assessment_intro' => sanitize_textarea_field($settings['assessment_intro'] ?? $defaults['assessment_intro']),
            'dashboard_eyebrow' => sanitize_text_field($settings['dashboard_eyebrow'] ?? $defaults['dashboard_eyebrow']),
            'dashboard_intro' => sanitize_textarea_field($settings['dashboard_intro'] ?? $defaults['dashboard_intro']),
            'dashboard_outro' => sanitize_textarea_field($settings['dashboard_outro'] ?? $defaults['dashboard_outro']),
            'report_layout_mode' => $layout_mode,
            'report_max_width' => sanitize_text_field($report_max_width),
            'report_position' => $report_position,
            'refine_display' => $refine_display,
            'allow_retakes' => !empty($settings['allow_retakes']),
            'show_icons' => !empty($settings['show_icons']),
            'show_house_scores' => !empty($settings['show_house_scores']),
            'show_capture_overlay' => !empty($settings['show_capture_overlay']),
            'show_dimension_bars' => !empty($settings['show_dimension_bars']),
            'show_primary_secondary_cards' => !empty($settings['show_primary_secondary_cards']),
            'compact_mode' => !empty($settings['compact_mode']),
            'top_match_count' => $top_match_count,
            'cta_label' => sanitize_text_field($settings['cta_label'] ?? $defaults['cta_label']),
            'cta_url' => esc_url_raw($settings['cta_url'] ?? $defaults['cta_url']),
            'inherit_theme_styles' => !empty($settings['inherit_theme_styles']),
            'accent_color' => sanitize_hex_color($settings['accent_color'] ?? $defaults['accent_color']) ?: '',
            'accent_color_secondary' => sanitize_hex_color($settings['accent_color_secondary'] ?? $defaults['accent_color_secondary']) ?: '',
            'surface_color' => sanitize_hex_color($settings['surface_color'] ?? $defaults['surface_color']) ?: '',
            'panel_color' => sanitize_hex_color($settings['panel_color'] ?? $defaults['panel_color']) ?: '',
            'text_color' => sanitize_hex_color($settings['text_color'] ?? $defaults['text_color']) ?: '',
            'muted_color' => sanitize_hex_color($settings['muted_color'] ?? $defaults['muted_color']) ?: '',
            'border_radius' => max(0, min(40, absint($settings['border_radius'] ?? $defaults['border_radius']))),
            'panel_shadow' => $shadow,
            'custom_css' => wp_strip_all_tags((string) ($settings['custom_css'] ?? $defaults['custom_css']), true),
        );

        update_option('acb_settings', $clean);
        return $clean;
    }

    public function all_questions($include_inactive = true)
    {
        global $wpdb;

        $questions = ACB_Schema::table('questions');
        $where = $include_inactive ? '1=1' : 'active = 1';
        $rows = $wpdb->get_results("SELECT * FROM {$questions} WHERE {$where} ORDER BY sort_order ASC, id ASC", ARRAY_A);

        return array_map(array($this, 'hydrate_question'), $rows);
    }

    public function get_active_questions($limit = 10, $profile_id = 0)
    {
        global $wpdb;

        $limit = max(1, absint($limit));
        $questions = ACB_Schema::table('questions');
        $answers = ACB_Schema::table('answers');

        if ($profile_id > 0) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT q.* FROM {$questions} q
                 WHERE q.active = 1
                 AND NOT EXISTS (
                    SELECT 1 FROM {$answers} a
                    WHERE a.question_id = q.id AND a.profile_id = %d
                 )
                 ORDER BY q.sort_order ASC, q.id ASC
                 LIMIT %d",
                (int) $profile_id,
                $limit
            ), ARRAY_A);
        } else {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$questions} WHERE active = 1 ORDER BY sort_order ASC, id ASC LIMIT %d",
                $limit
            ), ARRAY_A);
        }

        return array_map(array($this, 'hydrate_question'), $rows);
    }

    public function get_question($id)
    {
        global $wpdb;

        $table = ACB_Schema::table('questions');
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", (int) $id), ARRAY_A);

        return $row ? $this->hydrate_question($row) : null;
    }

    public function get_question_by_key($question_key)
    {
        global $wpdb;

        $table = ACB_Schema::table('questions');
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE question_key = %s", sanitize_key($question_key)), ARRAY_A);

        return $row ? $this->hydrate_question($row) : null;
    }

    public function save_question(array $question)
    {
        global $wpdb;

        $question = $this->normalize_question($question);
        if (empty($question['question_key'])) {
            return 0;
        }

        $table = ACB_Schema::table('questions');
        $now = current_time('mysql');
        $id = (int) ($question['id'] ?? 0);
        $data = array(
            'question_key' => $question['question_key'],
            'prompt' => $question['prompt'],
            'context' => $question['context'],
            'question_type' => $question['question_type'],
            'required' => !empty($question['required']) ? 1 : 0,
            'active' => !empty($question['active']) ? 1 : 0,
            'pack' => $question['pack'],
            'domain' => $question['domain'],
            'role' => $question['role'],
            'min_answers' => (int) $question['min_answers'],
            'max_answers' => (int) $question['max_answers'],
            'sort_order' => (int) $question['sort_order'],
            'config_json' => wp_json_encode($question),
            'updated_at' => $now,
        );

        if ($id > 0 && $this->get_question($id)) {
            $wpdb->update($table, $data, array('id' => $id));
        } else {
            $existing = $this->get_question_by_key($question['question_key']);
            if ($existing) {
                $id = (int) $existing['id'];
                $wpdb->update($table, $data, array('id' => $id));
            } else {
                $data['created_at'] = $now;
                $wpdb->insert($table, $data);
                $id = (int) $wpdb->insert_id;
            }
        }

        $this->save_question_options($id, $question['options']);
        return $id;
    }

    public function delete_question($id)
    {
        global $wpdb;

        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        $wpdb->delete(ACB_Schema::table('options'), array('question_id' => $id));
        $wpdb->delete(ACB_Schema::table('answers'), array('question_id' => $id));

        return $wpdb->delete(ACB_Schema::table('questions'), array('id' => $id));
    }

    public function import_questions(array $questions, $overwrite = false)
    {
        $saved = 0;
        foreach ($questions as $index => $question) {
            if (!is_array($question)) {
                continue;
            }

            if (!isset($question['sort_order'])) {
                $question['sort_order'] = $index + 1;
            }

            $existing = $this->get_question_by_key($question['question_key'] ?? $question['key'] ?? '');
            if ($existing && !$overwrite) {
                continue;
            }

            if ($existing) {
                $question['id'] = (int) $existing['id'];
            }

            if ($this->save_question($question)) {
                $saved++;
            }
        }

        return $saved;
    }

    public function export_questions()
    {
        $questions = $this->all_questions(true);
        foreach ($questions as &$question) {
            unset($question['id']);
        }

        return array(
            'schema' => 'american-civic-bestiary-question-pack-v1',
            'exported_at' => current_time('mysql'),
            'questions' => $questions,
        );
    }

    public function profile_key_from_token($token)
    {
        $token = is_scalar($token) ? (string) $token : '';
        return hash('sha256', wp_salt('auth') . '|' . $token);
    }

    public function find_or_create_profile($profile_key, $user_id = 0)
    {
        global $wpdb;

        $table = ACB_Schema::table('profiles');
        $user_id = (int) $user_id;
        $profile_key = sanitize_text_field($profile_key);
        $profile_row = null;

        if ($profile_key) {
            $profile_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE profile_key = %s LIMIT 1", $profile_key), ARRAY_A);
        }

        if ($user_id > 0 && $profile_row && !empty($profile_row['user_id']) && (int) $profile_row['user_id'] !== $user_id) {
            $profile_row = null;
            $profile_key = $this->profile_key_from_token('user-' . $user_id . '-' . wp_generate_password(24, false, false));
        }

        if ($user_id > 0) {
            $user_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE user_id = %d ORDER BY updated_at DESC LIMIT 1", $user_id), ARRAY_A);

            if ($user_row) {
                if ($profile_row && (int) $profile_row['id'] !== (int) $user_row['id']) {
                    // A logged-in visitor may arrive with an anonymous quiz cookie from before login.
                    // Merge that cookie-backed profile into the stable user profile, then repoint the
                    // cookie key so future anonymous-cookie lookups resolve to the same profile.
                    $this->merge_profiles((int) $profile_row['id'], (int) $user_row['id'], $profile_key);
                    return $this->get_profile((int) $user_row['id']);
                }

                if ($profile_key && $user_row['profile_key'] !== $profile_key) {
                    $this->replace_profile_key((int) $user_row['id'], $profile_key);
                    $user_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", (int) $user_row['id']), ARRAY_A);
                }

                return $this->hydrate_profile($user_row);
            }
        }

        if ($profile_row) {
            if ($user_id > 0 && empty($profile_row['user_id'])) {
                $wpdb->update($table, array('user_id' => $user_id, 'updated_at' => current_time('mysql')), array('id' => (int) $profile_row['id']));
                $profile_row['user_id'] = $user_id;
            }
            return $this->hydrate_profile($profile_row);
        }

        $now = current_time('mysql');
        $wpdb->insert($table, array(
            'user_id' => $user_id > 0 ? $user_id : null,
            'profile_key' => $profile_key,
            'created_at' => $now,
            'updated_at' => $now,
        ));

        return $this->get_profile((int) $wpdb->insert_id);
    }

    private function merge_profiles($source_profile_id, $target_profile_id, $target_profile_key = '')
    {
        global $wpdb;

        $source_profile_id = (int) $source_profile_id;
        $target_profile_id = (int) $target_profile_id;
        if ($source_profile_id <= 0 || $target_profile_id <= 0 || $source_profile_id === $target_profile_id) {
            return;
        }

        $profiles = ACB_Schema::table('profiles');
        $answers = ACB_Schema::table('answers');
        $snapshots = ACB_Schema::table('snapshots');
        $source = $this->get_profile($source_profile_id);
        $target = $this->get_profile($target_profile_id);
        if (!$source || !$target) {
            return;
        }

        $source_answers = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$answers} WHERE profile_id = %d ORDER BY answered_at ASC, id ASC",
            $source_profile_id
        ), ARRAY_A);

        foreach ($source_answers as $answer) {
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$answers} WHERE profile_id = %d AND question_id = %d LIMIT 1",
                $target_profile_id,
                (int) $answer['question_id']
            ));

            if ($existing_id) {
                continue;
            }

            unset($answer['id']);
            $answer['profile_id'] = $target_profile_id;
            $wpdb->insert($answers, $answer);
        }

        $identity = array('updated_at' => current_time('mysql'));
        if (empty($target['respondent_name']) && !empty($source['respondent_name'])) {
            $identity['respondent_name'] = sanitize_text_field($source['respondent_name']);
        }
        if (empty($target['respondent_email']) && !empty($source['respondent_email']) && is_email($source['respondent_email'])) {
            $identity['respondent_email'] = sanitize_email($source['respondent_email']);
        }

        $identity['total_answered'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$answers} WHERE profile_id = %d", $target_profile_id));
        $identity['latest_result_json'] = '';
        $wpdb->update($profiles, $identity, array('id' => $target_profile_id));
        $wpdb->update($snapshots, array('profile_id' => $target_profile_id), array('profile_id' => $source_profile_id));

        if ($target_profile_key) {
            $this->replace_profile_key($target_profile_id, $target_profile_key, $source_profile_id);
        }

        $wpdb->delete($answers, array('profile_id' => $source_profile_id));
        $wpdb->delete($profiles, array('id' => $source_profile_id));
    }

    private function replace_profile_key($profile_id, $profile_key, $known_conflict_id = 0)
    {
        global $wpdb;

        $profile_id = (int) $profile_id;
        $profile_key = sanitize_text_field($profile_key);
        if ($profile_id <= 0 || !$profile_key) {
            return;
        }

        $profiles = ACB_Schema::table('profiles');
        $conflict = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$profiles} WHERE profile_key = %s AND id <> %d LIMIT 1",
            $profile_key,
            $profile_id
        ));

        if ($conflict > 0) {
            $wpdb->update($profiles, array(
                'profile_key' => 'merged-' . $conflict . '-' . wp_generate_password(20, false, false),
                'updated_at' => current_time('mysql'),
            ), array('id' => $known_conflict_id ? (int) $known_conflict_id : $conflict));
        }

        $wpdb->update($profiles, array('profile_key' => $profile_key, 'updated_at' => current_time('mysql')), array('id' => $profile_id));
    }

    public function update_profile_identity($profile_id, $name, $email)
    {
        global $wpdb;

        $data = array('updated_at' => current_time('mysql'));
        if ('' !== trim((string) $name)) {
            $data['respondent_name'] = sanitize_text_field($name);
        }
        if ('' !== trim((string) $email) && is_email($email)) {
            $data['respondent_email'] = sanitize_email($email);
        }

        if (count($data) > 1) {
            $wpdb->update(ACB_Schema::table('profiles'), $data, array('id' => (int) $profile_id));
        }
    }

    public function get_profile($profile_id)
    {
        global $wpdb;

        $table = ACB_Schema::table('profiles');
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", (int) $profile_id), ARRAY_A);

        return $row ? $this->hydrate_profile($row) : null;
    }

    public function profiles($limit = 100)
    {
        global $wpdb;

        $table = ACB_Schema::table('profiles');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY updated_at DESC LIMIT %d",
            max(1, absint($limit))
        ), ARRAY_A);

        return array_map(array($this, 'hydrate_profile'), $rows);
    }

    public function answers_for_profile($profile_id)
    {
        global $wpdb;

        $table = ACB_Schema::table('answers');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE profile_id = %d ORDER BY answered_at ASC, id ASC",
            (int) $profile_id
        ), ARRAY_A);

        return array_map(array($this, 'hydrate_answer'), $rows);
    }

    public function answered_question_ids($profile_id)
    {
        global $wpdb;

        $table = ACB_Schema::table('answers');
        $ids = $wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$table} WHERE profile_id = %d", (int) $profile_id));

        return array_map('intval', $ids);
    }

    public function build_answer_delta(array $question, $answer)
    {
        $type = $question['question_type'] ?? 'single_choice';
        $selected = array();
        $ranked = array();

        if ('multi_choice' === $type) {
            $selected = array_values(array_filter(array_map('sanitize_key', (array) $answer)));
        } elseif ('ranked_choice' === $type && is_array($answer)) {
            foreach ($answer as $option_key => $rank) {
                $rank = absint($rank);
                if ($rank > 0) {
                    $ranked[sanitize_key($option_key)] = $rank;
                }
            }
            asort($ranked);
            $selected = array_keys($ranked);
        } else {
            $value = sanitize_key(is_array($answer) ? '' : $answer);
            if ($value) {
                $selected = array($value);
            }
        }

        $dimension_delta = array();
        $animal_delta = array();
        $updown_delta = array();
        $answer_payload = array(
            'type' => $type,
            'selected' => $selected,
        );
        if ($ranked) {
            $answer_payload['ranks'] = $ranked;
        }

        $option_map = array();
        foreach (($question['options'] ?? array()) as $option) {
            $option_map[$option['option_key']] = $option;
        }

        $rank_count = max(1, count($selected));
        foreach ($selected as $position => $option_key) {
            if (!isset($option_map[$option_key])) {
                continue;
            }

            $weight = 1.0;
            if ('ranked_choice' === $type) {
                $rank = (int) ($ranked[$option_key] ?? ($position + 1));
                $weight = max(0.1, ($rank_count - $rank + 1) / $rank_count);
            }

            $this->add_scaled_map($dimension_delta, $option_map[$option_key]['dimension_scores'] ?? array(), $weight);
            $this->add_scaled_map($animal_delta, $option_map[$option_key]['animal_scores'] ?? array(), $weight);
            $this->add_scaled_map($updown_delta, $option_map[$option_key]['updown_scores'] ?? array(), $weight);
        }

        return array(
            'answer' => $answer_payload,
            'dimension_delta' => $dimension_delta,
            'animal_delta' => $animal_delta,
            'updown_delta' => $updown_delta,
        );
    }

    public function save_answer($profile_id, array $question, array $delta)
    {
        global $wpdb;

        $table = ACB_Schema::table('answers');
        $profile_id = (int) $profile_id;
        $question_id = (int) ($question['id'] ?? 0);
        if ($profile_id <= 0 || $question_id <= 0) {
            return 0;
        }

        $now = current_time('mysql');
        $data = array(
            'profile_id' => $profile_id,
            'question_id' => $question_id,
            'question_key' => sanitize_key($question['question_key'] ?? ''),
            'answer_json' => wp_json_encode($delta['answer'] ?? array()),
            'dimension_delta_json' => wp_json_encode($delta['dimension_delta'] ?? array()),
            'animal_delta_json' => wp_json_encode($delta['animal_delta'] ?? array()),
            'updown_delta_json' => wp_json_encode($delta['updown_delta'] ?? array()),
            'answered_at' => $now,
        );

        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE profile_id = %d AND question_id = %d",
            $profile_id,
            $question_id
        ));

        if ($existing_id) {
            $wpdb->update($table, $data, array('id' => (int) $existing_id));
            return (int) $existing_id;
        }

        $wpdb->insert($table, $data);
        return (int) $wpdb->insert_id;
    }

    public function save_profile_result($profile_id, array $result)
    {
        global $wpdb;

        $profile_id = (int) $profile_id;
        $profile_table = ACB_Schema::table('profiles');
        $snapshot_table = ACB_Schema::table('snapshots');
        $primary = $result['animals']['primary']['key'] ?? '';
        $secondary = $result['animals']['secondary']['key'] ?? '';
        $house = $result['house']['key'] ?? '';
        $updown = (float) ($result['updown']['index'] ?? 0);
        $total_answered = (int) ($result['meta']['total_answered'] ?? 0);
        $now = current_time('mysql');

        $wpdb->update($profile_table, array(
            'total_answered' => $total_answered,
            'completion_percent' => (float) ($result['meta']['completion_percent'] ?? 0),
            'current_primary_animal' => sanitize_key($primary),
            'current_secondary_animal' => sanitize_key($secondary),
            'current_house' => sanitize_key($house),
            'confidence_label' => sanitize_key($result['meta']['confidence_label'] ?? ''),
            'updown_index' => $updown,
            'latest_result_json' => wp_json_encode($result),
            'updated_at' => $now,
        ), array('id' => $profile_id));

        $wpdb->insert($snapshot_table, array(
            'profile_id' => $profile_id,
            'total_answered' => $total_answered,
            'primary_animal' => sanitize_key($primary),
            'secondary_animal' => sanitize_key($secondary),
            'house_key' => sanitize_key($house),
            'updown_index' => $updown,
            'result_json' => wp_json_encode($result),
            'created_at' => $now,
        ));
    }

    public function count_questions($active_only = true)
    {
        global $wpdb;

        $table = ACB_Schema::table('questions');
        if ($active_only) {
            return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE active = 1");
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    public function count_profiles()
    {
        global $wpdb;

        $table = ACB_Schema::table('profiles');
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    public function count_answers()
    {
        global $wpdb;

        $table = ACB_Schema::table('answers');
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    public function latest_snapshots($profile_id, $limit = 20)
    {
        global $wpdb;

        $table = ACB_Schema::table('snapshots');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE profile_id = %d ORDER BY created_at DESC LIMIT %d",
            (int) $profile_id,
            max(1, absint($limit))
        ), ARRAY_A);

        foreach ($rows as &$row) {
            $row['result'] = $this->decode_json($row['result_json']);
        }

        return $rows;
    }

    private function normalize_question(array $question)
    {
        $type = sanitize_key($question['question_type'] ?? $question['type'] ?? 'single_choice');
        if (!in_array($type, array('single_choice', 'multi_choice', 'ranked_choice'), true)) {
            $type = 'single_choice';
        }

        $key = sanitize_key($question['question_key'] ?? $question['key'] ?? '');
        $normalized = array(
            'id' => (int) ($question['id'] ?? 0),
            'question_key' => $key,
            'prompt' => wp_kses_post($question['prompt'] ?? $question['label'] ?? $key),
            'context' => wp_kses_post($question['context'] ?? $question['help'] ?? ''),
            'question_type' => $type,
            'required' => array_key_exists('required', $question) ? !empty($question['required']) : true,
            'active' => array_key_exists('active', $question) ? !empty($question['active']) : true,
            'pack' => sanitize_key($question['pack'] ?? ($question['meta']['pack'] ?? 'core_v1')),
            'domain' => sanitize_key($question['domain'] ?? ($question['meta']['domain'] ?? '')),
            'role' => sanitize_key($question['role'] ?? ($question['meta']['role'] ?? '')),
            'min_answers' => max(1, absint($question['min_answers'] ?? 1)),
            'max_answers' => max(1, absint($question['max_answers'] ?? ('multi_choice' === $type ? 3 : 1))),
            'sort_order' => (int) ($question['sort_order'] ?? 0),
            'options' => array(),
        );

        foreach (($question['options'] ?? array()) as $index => $option) {
            if (!is_array($option)) {
                continue;
            }
            $option_key = sanitize_key($option['option_key'] ?? $option['key'] ?? '');
            if (!$option_key) {
                continue;
            }
            $normalized['options'][] = array(
                'option_key' => $option_key,
                'label' => sanitize_text_field($option['label'] ?? $option_key),
                'dimension_scores' => $this->clean_score_map($option['dimension_scores'] ?? $option['scores'] ?? array()),
                'animal_scores' => $this->clean_score_map($option['animal_scores'] ?? array()),
                'updown_scores' => $this->clean_score_map($option['updown_scores'] ?? array()),
                'sort_order' => (int) ($option['sort_order'] ?? $index),
            );
        }

        return $normalized;
    }

    private function save_question_options($question_id, array $options)
    {
        global $wpdb;

        $table = ACB_Schema::table('options');
        $wpdb->delete($table, array('question_id' => (int) $question_id));
        $now = current_time('mysql');

        foreach ($options as $option) {
            $wpdb->insert($table, array(
                'question_id' => (int) $question_id,
                'option_key' => sanitize_key($option['option_key'] ?? ''),
                'label' => sanitize_text_field($option['label'] ?? ''),
                'dimension_scores_json' => wp_json_encode($option['dimension_scores'] ?? array()),
                'animal_scores_json' => wp_json_encode($option['animal_scores'] ?? array()),
                'updown_scores_json' => wp_json_encode($option['updown_scores'] ?? array()),
                'sort_order' => (int) ($option['sort_order'] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ));
        }
    }

    private function hydrate_question(array $row)
    {
        global $wpdb;

        $question = array(
            'id' => (int) $row['id'],
            'question_key' => $row['question_key'],
            'prompt' => $row['prompt'],
            'context' => $row['context'],
            'question_type' => $row['question_type'],
            'required' => !empty($row['required']),
            'active' => !empty($row['active']),
            'pack' => $row['pack'],
            'domain' => $row['domain'],
            'role' => $row['role'],
            'min_answers' => (int) $row['min_answers'],
            'max_answers' => (int) $row['max_answers'],
            'sort_order' => (int) $row['sort_order'],
            'options' => array(),
        );

        $options_table = ACB_Schema::table('options');
        $options = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$options_table} WHERE question_id = %d ORDER BY sort_order ASC, id ASC",
            (int) $row['id']
        ), ARRAY_A);

        foreach ($options as $option) {
            $question['options'][] = array(
                'id' => (int) $option['id'],
                'question_id' => (int) $option['question_id'],
                'option_key' => $option['option_key'],
                'label' => $option['label'],
                'dimension_scores' => $this->decode_json($option['dimension_scores_json']),
                'animal_scores' => $this->decode_json($option['animal_scores_json']),
                'updown_scores' => $this->decode_json($option['updown_scores_json']),
                'sort_order' => (int) $option['sort_order'],
            );
        }

        return $question;
    }

    private function hydrate_profile(array $row)
    {
        $row['id'] = (int) $row['id'];
        $row['user_id'] = (int) $row['user_id'];
        $row['total_answered'] = (int) $row['total_answered'];
        $row['completion_percent'] = (float) $row['completion_percent'];
        $row['updown_index'] = (float) $row['updown_index'];
        $row['latest_result'] = $this->decode_json($row['latest_result_json'] ?? '');

        return $row;
    }

    private function hydrate_answer(array $row)
    {
        return array(
            'id' => (int) $row['id'],
            'profile_id' => (int) $row['profile_id'],
            'question_id' => (int) $row['question_id'],
            'question_key' => $row['question_key'],
            'answer' => $this->decode_json($row['answer_json']),
            'dimension_delta' => $this->decode_json($row['dimension_delta_json']),
            'animal_delta' => $this->decode_json($row['animal_delta_json']),
            'updown_delta' => $this->decode_json($row['updown_delta_json']),
            'answered_at' => $row['answered_at'],
        );
    }

    private function add_scaled_map(array &$target, array $source, $scale)
    {
        foreach ($source as $key => $value) {
            $key = sanitize_key($key);
            if (!$key || !is_numeric($value)) {
                continue;
            }
            if (!isset($target[$key])) {
                $target[$key] = 0.0;
            }
            $target[$key] += (float) $value * (float) $scale;
        }
    }

    private function clean_score_map($map)
    {
        if (is_string($map)) {
            $map = $this->parse_score_pairs($map);
        }

        if (!is_array($map)) {
            return array();
        }

        $clean = array();
        foreach ($map as $key => $value) {
            $key = sanitize_key($key);
            if ($key && is_numeric($value)) {
                $clean[$key] = (float) $value;
            }
        }

        return $clean;
    }

    private function parse_score_pairs($text)
    {
        $scores = array();
        foreach (explode(',', (string) $text) as $pair) {
            $parts = array_map('trim', explode(':', $pair, 2));
            if (2 !== count($parts)) {
                continue;
            }
            $key = sanitize_key($parts[0]);
            if ($key && is_numeric($parts[1])) {
                $scores[$key] = (float) $parts[1];
            }
        }

        return $scores;
    }

    private function decode_json($json)
    {
        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? $decoded : array();
    }
}
