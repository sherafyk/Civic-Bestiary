<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Admin
{
    private $repository;
    private $scoring_engine;

    public function __construct(ACB_Repository $repository, ACB_Scoring_Engine $scoring_engine)
    {
        $this->repository = $repository;
        $this->scoring_engine = $scoring_engine;
    }

    public function register_hooks()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_post_acb_save_question', array($this, 'save_question'));
        add_action('admin_post_acb_delete_question', array($this, 'delete_question'));
        add_action('admin_post_acb_seed_starter', array($this, 'seed_starter'));
        add_action('admin_post_acb_export_questions', array($this, 'export_questions'));
        add_action('admin_post_acb_export_profiles', array($this, 'export_profiles'));
        add_action('admin_post_acb_save_settings', array($this, 'save_settings'));
    }

    public function admin_menu()
    {
        add_menu_page(
            __('Civic Bestiary', 'american-civic-bestiary'),
            __('Civic Bestiary', 'american-civic-bestiary'),
            'manage_options',
            'acb',
            array($this, 'render_dashboard_page'),
            'dashicons-shield-alt'
        );

        add_submenu_page('acb', __('Overview', 'american-civic-bestiary'), __('Overview', 'american-civic-bestiary'), 'manage_options', 'acb', array($this, 'render_dashboard_page'));
        add_submenu_page('acb', __('Questions', 'american-civic-bestiary'), __('Questions', 'american-civic-bestiary'), 'manage_options', 'acb-questions', array($this, 'render_questions_page'));
        add_submenu_page('acb', __('Profiles', 'american-civic-bestiary'), __('Profiles', 'american-civic-bestiary'), 'manage_options', 'acb-profiles', array($this, 'render_profiles_page'));
        add_submenu_page('acb', __('Settings', 'american-civic-bestiary'), __('Settings', 'american-civic-bestiary'), 'manage_options', 'acb-settings', array($this, 'render_settings_page'));
    }

    public function enqueue($hook)
    {
        if (false !== strpos((string) $hook, 'acb')) {
            wp_enqueue_style('acb-admin');
            wp_enqueue_script('acb-admin');
        }
    }

    public function render_dashboard_page()
    {
        $this->require_admin();
        $animals = ACB_Core_Data::animals();
        ?>
        <div class="wrap acb-admin">
            <h1><?php esc_html_e('American Civic Bestiary', 'american-civic-bestiary'); ?></h1>
            <?php $this->render_notices(); ?>

            <div class="acb-admin-grid acb-admin-grid--stats">
                <?php $this->stat_card(__('Active questions', 'american-civic-bestiary'), $this->repository->count_questions(true)); ?>
                <?php $this->stat_card(__('Profiles', 'american-civic-bestiary'), $this->repository->count_profiles()); ?>
                <?php $this->stat_card(__('Answers', 'american-civic-bestiary'), $this->repository->count_answers()); ?>
                <?php $this->stat_card(__('Plugin version', 'american-civic-bestiary'), ACB_VERSION); ?>
            </div>

            <section class="acb-admin-panel">
                <h2><?php esc_html_e('Quick actions', 'american-civic-bestiary'); ?></h2>
                <div class="acb-button-row">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=acb-questions&view=edit')); ?>"><?php esc_html_e('Create Question', 'american-civic-bestiary'); ?></a>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=acb-settings')); ?>"><?php esc_html_e('Open Settings', 'american-civic-bestiary'); ?></a>
                    <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=acb_export_questions'), 'acb_export_questions')); ?>"><?php esc_html_e('Export Question Pack', 'american-civic-bestiary'); ?></a>
                    <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=acb_export_profiles'), 'acb_export_profiles')); ?>"><?php esc_html_e('Export Profiles CSV', 'american-civic-bestiary'); ?></a>
                </div>
            </section>

            <section class="acb-admin-panel">
                <h2><?php esc_html_e('Starter content', 'american-civic-bestiary'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('acb_seed_starter'); ?>
                    <input type="hidden" name="action" value="acb_seed_starter">
                    <label><input type="checkbox" name="overwrite" value="1"> <?php esc_html_e('Overwrite matching starter-question keys', 'american-civic-bestiary'); ?></label>
                    <?php submit_button(__('Import Starter Questions', 'american-civic-bestiary'), 'secondary', 'submit', false); ?>
                </form>
            </section>

            <section class="acb-admin-panel">
                <h2><?php esc_html_e('Bundled animal icon set', 'american-civic-bestiary'); ?></h2>
                <p><?php esc_html_e('These lightweight icons are bundled with the plugin and used in the premium report cards. You can override templates from your theme and replace icon URLs via the acb_animal_icon_url filter if needed.', 'american-civic-bestiary'); ?></p>
                <div class="acb-icon-grid">
                    <?php foreach ($animals as $key => $animal) : ?>
                        <figure class="acb-icon-card">
                            <img src="<?php echo esc_url(ACB_Assets::animal_icon_url($key)); ?>" alt="<?php echo esc_attr($animal['label']); ?>">
                            <figcaption>
                                <strong><?php echo esc_html($animal['label']); ?></strong>
                                <span><?php echo esc_html($animal['title']); ?></span>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
        <?php
    }

    public function render_questions_page()
    {
        $this->require_admin();
        $view = sanitize_key($_GET['view'] ?? 'list');
        if ('edit' === $view) {
            $this->render_question_editor(absint($_GET['question_id'] ?? 0));
            return;
        }

        $questions = $this->repository->all_questions(true);
        ?>
        <div class="wrap acb-admin">
            <h1 class="wp-heading-inline"><?php esc_html_e('Bestiary Questions', 'american-civic-bestiary'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=acb-questions&view=edit')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'american-civic-bestiary'); ?></a>
            <?php $this->render_notices(); ?>
            <section class="acb-admin-panel">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Prompt', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Key', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Domain', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Type', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Status', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Actions', 'american-civic-bestiary'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($questions) : ?>
                            <?php foreach ($questions as $question) : ?>
                                <?php $this->render_question_row($question); ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="6"><?php esc_html_e('No questions found.', 'american-civic-bestiary'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
        <?php
    }

    public function render_profiles_page()
    {
        $this->require_admin();
        $profile_id = absint($_GET['profile_id'] ?? 0);
        if ($profile_id) {
            $this->render_profile_detail($profile_id);
            return;
        }

        $profiles = $this->repository->profiles(200);
        ?>
        <div class="wrap acb-admin">
            <h1 class="wp-heading-inline"><?php esc_html_e('Bestiary Profiles', 'american-civic-bestiary'); ?></h1>
            <a class="page-title-action" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=acb_export_profiles'), 'acb_export_profiles')); ?>"><?php esc_html_e('Export CSV', 'american-civic-bestiary'); ?></a>
            <?php $this->render_notices(); ?>
            <section class="acb-admin-panel">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Profile', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Answers', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Primary', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('House', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Up-vs-Down', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Updated', 'american-civic-bestiary'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($profiles as $profile) : ?>
                            <tr>
                                <td><a href="<?php echo esc_url(admin_url('admin.php?page=acb-profiles&profile_id=' . (int) $profile['id'])); ?>"><?php echo esc_html($profile['respondent_name'] ?: ('#' . (int) $profile['id'])); ?></a><br><small><?php echo esc_html($profile['respondent_email']); ?></small></td>
                                <td><?php echo esc_html((int) $profile['total_answered']); ?></td>
                                <td><?php echo esc_html($this->animal_label($profile['current_primary_animal'])); ?></td>
                                <td><?php echo esc_html($this->house_label($profile['current_house'])); ?></td>
                                <td><?php echo esc_html(round((float) $profile['updown_index'])); ?></td>
                                <td><?php echo esc_html($profile['updated_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
        <?php
    }

    public function render_settings_page()
    {
        $this->require_admin();
        $settings = $this->repository->settings();
        ?>
        <div class="wrap acb-admin acb-settings-page">
            <h1><?php esc_html_e('Bestiary Settings', 'american-civic-bestiary'); ?></h1>
            <?php $this->render_notices(); ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('acb_save_settings'); ?>
                <input type="hidden" name="action" value="acb_save_settings">

                <section class="acb-admin-panel">
                    <h2><?php esc_html_e('Assessment flow', 'american-civic-bestiary'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="minimum_questions"><?php esc_html_e('Minimum questions for full profile', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="minimum_questions" type="number" min="1" name="settings[minimum_questions]" value="<?php echo esc_attr((int) $settings['minimum_questions']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="questions_per_session"><?php esc_html_e('Questions per session', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="questions_per_session" type="number" min="1" name="settings[questions_per_session]" value="<?php echo esc_attr((int) $settings['questions_per_session']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Identity fields', 'american-civic-bestiary'); ?></th>
                            <td>
                                <label><input type="checkbox" name="settings[show_name_field]" value="1" <?php checked(!empty($settings['show_name_field'])); ?>> <?php esc_html_e('Show name field', 'american-civic-bestiary'); ?></label><br>
                                <label><input type="checkbox" name="settings[show_email_field]" value="1" <?php checked(!empty($settings['show_email_field'])); ?>> <?php esc_html_e('Show email field', 'american-civic-bestiary'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="consent_text"><?php esc_html_e('Consent text', 'american-civic-bestiary'); ?></label></th>
                            <td><textarea id="consent_text" class="large-text" rows="4" name="settings[consent_text]"><?php echo esc_textarea($settings['consent_text']); ?></textarea></td>
                        </tr>
                    </table>
                </section>

                <section class="acb-admin-panel">
                    <h2><?php esc_html_e('Frontend copy', 'american-civic-bestiary'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="assessment_eyebrow"><?php esc_html_e('Assessment eyebrow', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="assessment_eyebrow" class="regular-text" type="text" name="settings[assessment_eyebrow]" value="<?php echo esc_attr($settings['assessment_eyebrow']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="assessment_title"><?php esc_html_e('Assessment title', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="assessment_title" class="large-text" type="text" name="settings[assessment_title]" value="<?php echo esc_attr($settings['assessment_title']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="assessment_intro"><?php esc_html_e('Assessment intro', 'american-civic-bestiary'); ?></label></th>
                            <td><textarea id="assessment_intro" class="large-text" rows="3" name="settings[assessment_intro]"><?php echo esc_textarea($settings['assessment_intro']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="dashboard_eyebrow"><?php esc_html_e('Report eyebrow', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="dashboard_eyebrow" class="regular-text" type="text" name="settings[dashboard_eyebrow]" value="<?php echo esc_attr($settings['dashboard_eyebrow']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="dashboard_intro"><?php esc_html_e('Report intro override', 'american-civic-bestiary'); ?></label></th>
                            <td><textarea id="dashboard_intro" class="large-text" rows="3" name="settings[dashboard_intro]"><?php echo esc_textarea($settings['dashboard_intro']); ?></textarea><p class="description"><?php esc_html_e('Leave blank to use the profile animal summary automatically.', 'american-civic-bestiary'); ?></p></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cta_label"><?php esc_html_e('Optional call-to-action label', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="cta_label" class="regular-text" type="text" name="settings[cta_label]" value="<?php echo esc_attr($settings['cta_label']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cta_url"><?php esc_html_e('Optional call-to-action URL', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="cta_url" class="large-text" type="url" name="settings[cta_url]" value="<?php echo esc_attr($settings['cta_url']); ?>"></td>
                        </tr>
                    </table>
                </section>

                <section class="acb-admin-panel">
                    <h2><?php esc_html_e('Report display', 'american-civic-bestiary'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Display options', 'american-civic-bestiary'); ?></th>
                            <td>
                                <label><input type="checkbox" name="settings[show_icons]" value="1" <?php checked(!empty($settings['show_icons'])); ?>> <?php esc_html_e('Show bundled animal icons in report cards', 'american-civic-bestiary'); ?></label><br>
                                <label><input type="checkbox" name="settings[show_house_scores]" value="1" <?php checked(!empty($settings['show_house_scores'])); ?>> <?php esc_html_e('Show house alignment scores', 'american-civic-bestiary'); ?></label><br>
                                <label><input type="checkbox" name="settings[show_capture_overlay]" value="1" <?php checked(!empty($settings['show_capture_overlay'])); ?>> <?php esc_html_e('Show capture-literacy overlay', 'american-civic-bestiary'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="top_match_count"><?php esc_html_e('Top animal matches to show', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="top_match_count" type="number" min="3" max="16" name="settings[top_match_count]" value="<?php echo esc_attr((int) $settings['top_match_count']); ?>"></td>
                        </tr>
                    </table>
                </section>

                <section class="acb-admin-panel">
                    <h2><?php esc_html_e('Appearance & theme integration', 'american-civic-bestiary'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Theme integration', 'american-civic-bestiary'); ?></th>
                            <td><label><input type="checkbox" name="settings[inherit_theme_styles]" value="1" <?php checked(!empty($settings['inherit_theme_styles'])); ?>> <?php esc_html_e('Inherit typography and foundational styling from the active theme', 'american-civic-bestiary'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="accent_color"><?php esc_html_e('Accent color', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="accent_color" type="color" name="settings[accent_color]" value="<?php echo esc_attr($settings['accent_color'] ?: '#245c73'); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="accent_color_secondary"><?php esc_html_e('Accent alt color', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="accent_color_secondary" type="color" name="settings[accent_color_secondary]" value="<?php echo esc_attr($settings['accent_color_secondary'] ?: '#0f766e'); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="surface_color"><?php esc_html_e('Surface color', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="surface_color" type="color" name="settings[surface_color]" value="<?php echo esc_attr($settings['surface_color'] ?: '#f8fafc'); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="panel_color"><?php esc_html_e('Panel color', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="panel_color" type="color" name="settings[panel_color]" value="<?php echo esc_attr($settings['panel_color'] ?: '#ffffff'); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="text_color"><?php esc_html_e('Text color', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="text_color" type="color" name="settings[text_color]" value="<?php echo esc_attr($settings['text_color'] ?: '#0f172a'); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="muted_color"><?php esc_html_e('Muted text color', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="muted_color" type="color" name="settings[muted_color]" value="<?php echo esc_attr($settings['muted_color'] ?: '#475569'); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="border_radius"><?php esc_html_e('Border radius', 'american-civic-bestiary'); ?></label></th>
                            <td><input id="border_radius" type="number" min="0" max="40" name="settings[border_radius]" value="<?php echo esc_attr((int) $settings['border_radius']); ?>"> <span class="description">px</span></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="panel_shadow"><?php esc_html_e('Panel shadow', 'american-civic-bestiary'); ?></label></th>
                            <td>
                                <select id="panel_shadow" name="settings[panel_shadow]">
                                    <option value="none" <?php selected($settings['panel_shadow'], 'none'); ?>><?php esc_html_e('None', 'american-civic-bestiary'); ?></option>
                                    <option value="small" <?php selected($settings['panel_shadow'], 'small'); ?>><?php esc_html_e('Small', 'american-civic-bestiary'); ?></option>
                                    <option value="medium" <?php selected($settings['panel_shadow'], 'medium'); ?>><?php esc_html_e('Medium', 'american-civic-bestiary'); ?></option>
                                    <option value="large" <?php selected($settings['panel_shadow'], 'large'); ?>><?php esc_html_e('Large', 'american-civic-bestiary'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="custom_css"><?php esc_html_e('Custom CSS', 'american-civic-bestiary'); ?></label></th>
                            <td><textarea id="custom_css" class="large-text code" rows="8" name="settings[custom_css]"><?php echo esc_textarea($settings['custom_css']); ?></textarea><p class="description"><?php esc_html_e('Applied only to the Bestiary frontend components after the plugin stylesheet loads.', 'american-civic-bestiary'); ?></p></td>
                        </tr>
                    </table>
                </section>

                <?php submit_button(__('Save Settings', 'american-civic-bestiary')); ?>
            </form>
        </div>
        <?php
    }

    public function save_question()
    {
        $this->require_admin();
        check_admin_referer('acb_save_question');

        $question = isset($_POST['question']) && is_array($_POST['question']) ? wp_unslash($_POST['question']) : array();
        $question['id'] = absint($_POST['question_id'] ?? 0);
        $question['required'] = !empty($question['required']);
        $question['active'] = !empty($question['active']);
        $question['options'] = $this->posted_options();

        $id = $this->repository->save_question($question);
        wp_safe_redirect(admin_url('admin.php?page=acb-questions&view=edit&question_id=' . (int) $id . '&saved=1'));
        exit;
    }

    public function delete_question()
    {
        $this->require_admin();
        $id = absint($_GET['question_id'] ?? 0);
        check_admin_referer('acb_delete_question_' . $id);
        $this->repository->delete_question($id);
        wp_safe_redirect(admin_url('admin.php?page=acb-questions&deleted=1'));
        exit;
    }

    public function seed_starter()
    {
        $this->require_admin();
        check_admin_referer('acb_seed_starter');
        $count = ACB_Activator::seed_starter_questions(!empty($_POST['overwrite']));
        wp_safe_redirect(admin_url('admin.php?page=acb&seeded=' . (int) $count));
        exit;
    }

    public function export_questions()
    {
        $this->require_admin();
        check_admin_referer('acb_export_questions');
        $payload = $this->repository->export_questions();

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=american-civic-bestiary-question-pack.json');
        echo wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function export_profiles()
    {
        $this->require_admin();
        check_admin_referer('acb_export_profiles');

        $profiles = $this->repository->profiles(5000);
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=american-civic-bestiary-profiles.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Name', 'Email', 'Answers', 'Primary Animal', 'Secondary Animal', 'House', 'Confidence', 'UpDown Index', 'Updated'));
        foreach ($profiles as $profile) {
            fputcsv($output, array(
                (int) $profile['id'],
                $profile['respondent_name'],
                $profile['respondent_email'],
                (int) $profile['total_answered'],
                $this->animal_label($profile['current_primary_animal']),
                $this->animal_label($profile['current_secondary_animal']),
                $this->house_label($profile['current_house']),
                $profile['confidence_label'],
                round((float) $profile['updown_index'], 2),
                $profile['updated_at'],
            ));
        }
        fclose($output);
        exit;
    }

    public function save_settings()
    {
        $this->require_admin();
        check_admin_referer('acb_save_settings');
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? wp_unslash($_POST['settings']) : array();
        $this->repository->update_settings($settings);
        wp_safe_redirect(admin_url('admin.php?page=acb-settings&settings_saved=1'));
        exit;
    }

    private function render_question_editor($question_id)
    {
        $question = $question_id ? $this->repository->get_question($question_id) : $this->blank_question();
        if (!$question) {
            wp_die(esc_html__('Question not found.', 'american-civic-bestiary'));
        }
        ?>
        <div class="wrap acb-admin">
            <h1><?php echo esc_html($question_id ? __('Edit Bestiary Question', 'american-civic-bestiary') : __('New Bestiary Question', 'american-civic-bestiary')); ?></h1>
            <?php $this->render_notices(); ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="acb-admin-panel">
                <?php wp_nonce_field('acb_save_question'); ?>
                <input type="hidden" name="action" value="acb_save_question">
                <input type="hidden" name="question_id" value="<?php echo esc_attr((int) $question['id']); ?>">

                <div class="acb-admin-grid">
                    <p>
                        <label><?php esc_html_e('Question key', 'american-civic-bestiary'); ?></label>
                        <input class="regular-text" type="text" name="question[question_key]" value="<?php echo esc_attr($question['question_key']); ?>" required>
                    </p>
                    <p>
                        <label><?php esc_html_e('Type', 'american-civic-bestiary'); ?></label>
                        <select name="question[question_type]">
                            <option value="single_choice" <?php selected($question['question_type'], 'single_choice'); ?>><?php esc_html_e('Single choice', 'american-civic-bestiary'); ?></option>
                            <option value="multi_choice" <?php selected($question['question_type'], 'multi_choice'); ?>><?php esc_html_e('Multi choice', 'american-civic-bestiary'); ?></option>
                            <option value="ranked_choice" <?php selected($question['question_type'], 'ranked_choice'); ?>><?php esc_html_e('Ranked choice', 'american-civic-bestiary'); ?></option>
                        </select>
                    </p>
                    <p>
                        <label><?php esc_html_e('Min answers', 'american-civic-bestiary'); ?></label>
                        <input type="number" min="1" name="question[min_answers]" value="<?php echo esc_attr((int) $question['min_answers']); ?>">
                    </p>
                    <p>
                        <label><?php esc_html_e('Max answers', 'american-civic-bestiary'); ?></label>
                        <input type="number" min="1" name="question[max_answers]" value="<?php echo esc_attr((int) $question['max_answers']); ?>">
                    </p>
                    <p>
                        <label><?php esc_html_e('Pack', 'american-civic-bestiary'); ?></label>
                        <input class="regular-text" type="text" name="question[pack]" value="<?php echo esc_attr($question['pack']); ?>">
                    </p>
                    <p>
                        <label><?php esc_html_e('Domain', 'american-civic-bestiary'); ?></label>
                        <input class="regular-text" type="text" name="question[domain]" value="<?php echo esc_attr($question['domain']); ?>">
                    </p>
                    <p>
                        <label><?php esc_html_e('Role', 'american-civic-bestiary'); ?></label>
                        <input class="regular-text" type="text" name="question[role]" value="<?php echo esc_attr($question['role']); ?>">
                    </p>
                    <p>
                        <label><?php esc_html_e('Sort order', 'american-civic-bestiary'); ?></label>
                        <input type="number" name="question[sort_order]" value="<?php echo esc_attr((int) $question['sort_order']); ?>">
                    </p>
                </div>

                <p>
                    <label><?php esc_html_e('Prompt', 'american-civic-bestiary'); ?></label>
                    <textarea class="large-text" rows="3" name="question[prompt]" required><?php echo esc_textarea($question['prompt']); ?></textarea>
                </p>
                <p>
                    <label><?php esc_html_e('Context', 'american-civic-bestiary'); ?></label>
                    <textarea class="large-text" rows="4" name="question[context]"><?php echo esc_textarea($question['context']); ?></textarea>
                </p>
                <p>
                    <label><input type="checkbox" name="question[required]" value="1" <?php checked(!empty($question['required'])); ?>> <?php esc_html_e('Required', 'american-civic-bestiary'); ?></label>
                    <label><input type="checkbox" name="question[active]" value="1" <?php checked(!empty($question['active'])); ?>> <?php esc_html_e('Active', 'american-civic-bestiary'); ?></label>
                </p>

                <h2><?php esc_html_e('Answer options and scoring', 'american-civic-bestiary'); ?></h2>
                <p><?php esc_html_e('Use comma-separated score maps like liberty:2, skepticism:1. Animal keys and Up-vs-Down subfactor keys use the same format.', 'american-civic-bestiary'); ?></p>
                <table class="widefat acb-options-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Key', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Label', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Dimension Scores', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Animal Scores', 'american-civic-bestiary'); ?></th>
                            <th><?php esc_html_e('Up-vs-Down Scores', 'american-civic-bestiary'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->option_rows($question) as $index => $option) : ?>
                            <tr>
                                <td><input type="text" name="options[<?php echo esc_attr($index); ?>][option_key]" value="<?php echo esc_attr($option['option_key'] ?? ''); ?>"></td>
                                <td><textarea name="options[<?php echo esc_attr($index); ?>][label]" rows="2"><?php echo esc_textarea($option['label'] ?? ''); ?></textarea></td>
                                <td><textarea name="options[<?php echo esc_attr($index); ?>][dimension_scores]" rows="2"><?php echo esc_textarea($this->score_string($option['dimension_scores'] ?? array())); ?></textarea></td>
                                <td><textarea name="options[<?php echo esc_attr($index); ?>][animal_scores]" rows="2"><?php echo esc_textarea($this->score_string($option['animal_scores'] ?? array())); ?></textarea></td>
                                <td><textarea name="options[<?php echo esc_attr($index); ?>][updown_scores]" rows="2"><?php echo esc_textarea($this->score_string($option['updown_scores'] ?? array())); ?></textarea></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button(__('Save Question', 'american-civic-bestiary')); ?>
            </form>
        </div>
        <?php
    }

    private function render_question_row(array $question)
    {
        $edit_url = admin_url('admin.php?page=acb-questions&view=edit&question_id=' . (int) $question['id']);
        $delete_url = wp_nonce_url(admin_url('admin-post.php?action=acb_delete_question&question_id=' . (int) $question['id']), 'acb_delete_question_' . (int) $question['id']);
        ?>
        <tr>
            <td><strong><a href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($question['prompt']), 18)); ?></a></strong></td>
            <td><code><?php echo esc_html($question['question_key']); ?></code></td>
            <td><?php echo esc_html($question['domain']); ?></td>
            <td><?php echo esc_html($question['question_type']); ?></td>
            <td><?php echo esc_html(!empty($question['active']) ? __('Active', 'american-civic-bestiary') : __('Inactive', 'american-civic-bestiary')); ?></td>
            <td><a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'american-civic-bestiary'); ?></a> | <a class="submitdelete" href="<?php echo esc_url($delete_url); ?>"><?php esc_html_e('Delete', 'american-civic-bestiary'); ?></a></td>
        </tr>
        <?php
    }

    private function render_profile_detail($profile_id)
    {
        $profile = $this->repository->get_profile($profile_id);
        if (!$profile) {
            wp_die(esc_html__('Profile not found.', 'american-civic-bestiary'));
        }
        $result = $profile['latest_result'];
        $primary = $profile['current_primary_animal'];
        ?>
        <div class="wrap acb-admin">
            <h1><?php echo esc_html(sprintf(__('Profile #%d', 'american-civic-bestiary'), (int) $profile['id'])); ?></h1>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=acb-profiles')); ?>"><?php esc_html_e('Back to profiles', 'american-civic-bestiary'); ?></a></p>
            <section class="acb-admin-panel acb-profile-detail">
                <div class="acb-profile-detail__head">
                    <?php if ($primary) : ?><img src="<?php echo esc_url(ACB_Assets::animal_icon_url($primary)); ?>" alt="<?php echo esc_attr($this->animal_label($primary)); ?>"><?php endif; ?>
                    <div>
                        <h2><?php echo esc_html($this->animal_label($profile['current_primary_animal'])); ?></h2>
                        <p><?php echo esc_html(sprintf(__('Answers: %d | Up-vs-Down: %s | Confidence: %s', 'american-civic-bestiary'), (int) $profile['total_answered'], round((float) $profile['updown_index']), $profile['confidence_label'])); ?></p>
                    </div>
                </div>
                <textarea class="large-text code" rows="28" readonly><?php echo esc_textarea(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></textarea>
            </section>
        </div>
        <?php
    }

    private function posted_options()
    {
        $rows = isset($_POST['options']) && is_array($_POST['options']) ? wp_unslash($_POST['options']) : array();
        $options = array();
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = sanitize_key($row['option_key'] ?? '');
            $label = sanitize_text_field($row['label'] ?? '');
            if (!$key || '' === $label) {
                continue;
            }
            $options[] = array(
                'option_key' => $key,
                'label' => $label,
                'dimension_scores' => $row['dimension_scores'] ?? '',
                'animal_scores' => $row['animal_scores'] ?? '',
                'updown_scores' => $row['updown_scores'] ?? '',
                'sort_order' => (int) $index,
            );
        }

        return $options;
    }

    private function option_rows(array $question)
    {
        $rows = $question['options'] ?? array();
        $blank_count = max(0, min(4, 10 - count($rows)));
        for ($i = 0; $i < $blank_count; $i++) {
            $rows[] = array('option_key' => '', 'label' => '', 'dimension_scores' => array(), 'animal_scores' => array(), 'updown_scores' => array());
        }

        return $rows;
    }

    private function blank_question()
    {
        return array(
            'id' => 0,
            'question_key' => '',
            'prompt' => '',
            'context' => '',
            'question_type' => 'single_choice',
            'required' => true,
            'active' => true,
            'pack' => 'core_v1',
            'domain' => '',
            'role' => 'anchor',
            'sort_order' => $this->repository->count_questions(false) + 1,
            'min_answers' => 1,
            'max_answers' => 1,
            'options' => array(),
        );
    }

    private function score_string(array $scores)
    {
        $parts = array();
        foreach ($scores as $key => $value) {
            $parts[] = sanitize_key($key) . ':' . $value;
        }

        return implode(', ', $parts);
    }

    private function stat_card($label, $value)
    {
        echo '<article class="acb-stat"><span>' . esc_html($label) . '</span><strong>' . esc_html($value) . '</strong></article>';
    }

    private function animal_label($key)
    {
        $animals = ACB_Core_Data::animals();
        return $animals[$key]['label'] ?? $key;
    }

    private function house_label($key)
    {
        $houses = ACB_Core_Data::houses();
        return $houses[$key]['label'] ?? $key;
    }

    private function render_notices()
    {
        if (!empty($_GET['saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Saved.', 'american-civic-bestiary') . '</p></div>';
        }
        if (!empty($_GET['deleted'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Deleted.', 'american-civic-bestiary') . '</p></div>';
        }
        if (isset($_GET['seeded'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(__('Starter questions imported: %d.', 'american-civic-bestiary'), absint($_GET['seeded']))) . '</p></div>';
        }
        if (!empty($_GET['settings_saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'american-civic-bestiary') . '</p></div>';
        }
    }

    private function require_admin()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage the American Civic Bestiary.', 'american-civic-bestiary'));
        }
    }
}
