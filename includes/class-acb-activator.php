<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Activator
{
    public static function activate($flush = true)
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach (ACB_Schema::sql() as $sql) {
            dbDelta($sql);
        }

        update_option('acb_db_version', ACB_VERSION);

        if (!get_option('acb_settings')) {
            add_option('acb_settings', ACB_Core_Data::settings_defaults());
        }

        self::seed_starter_questions(false);

        if ($flush) {
            flush_rewrite_rules();
        }
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    public static function seed_starter_questions($overwrite = false)
    {
        $file = ACB_PATH . 'starter-questions/core-v1.json';
        if (!file_exists($file)) {
            return 0;
        }

        $decoded = json_decode(file_get_contents($file), true);
        if (!is_array($decoded)) {
            return 0;
        }

        $questions = isset($decoded['questions']) && is_array($decoded['questions']) ? $decoded['questions'] : $decoded;
        $repository = new ACB_Repository();

        return $repository->import_questions($questions, $overwrite);
    }
}
