<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Schema
{
    public static function table($name)
    {
        global $wpdb;

        return $wpdb->prefix . 'acb_' . $name;
    }

    public static function tables()
    {
        return array(
            'questions' => self::table('questions'),
            'options' => self::table('options'),
            'profiles' => self::table('profiles'),
            'answers' => self::table('answers'),
            'snapshots' => self::table('snapshots'),
        );
    }

    public static function sql()
    {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $tables = self::tables();

        return array(
            "CREATE TABLE {$tables['questions']} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                question_key varchar(190) NOT NULL,
                prompt text NOT NULL,
                context text NULL,
                question_type varchar(40) NOT NULL DEFAULT 'single_choice',
                required tinyint(1) NOT NULL DEFAULT 1,
                active tinyint(1) NOT NULL DEFAULT 1,
                pack varchar(120) NULL,
                domain varchar(120) NULL,
                role varchar(120) NULL,
                min_answers int(11) NOT NULL DEFAULT 1,
                max_answers int(11) NOT NULL DEFAULT 1,
                sort_order int(11) NOT NULL DEFAULT 0,
                config_json longtext NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY question_key (question_key),
                KEY active_sort (active, sort_order),
                KEY pack (pack),
                KEY domain (domain)
            ) $charset;",

            "CREATE TABLE {$tables['options']} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                question_id bigint(20) unsigned NOT NULL,
                option_key varchar(190) NOT NULL,
                label text NOT NULL,
                dimension_scores_json longtext NULL,
                animal_scores_json longtext NULL,
                updown_scores_json longtext NULL,
                sort_order int(11) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY question_option (question_id, option_key),
                KEY question_id (question_id)
            ) $charset;",

            "CREATE TABLE {$tables['profiles']} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NULL,
                profile_key varchar(64) NOT NULL,
                respondent_email varchar(190) NULL,
                respondent_name varchar(190) NULL,
                total_answered int(11) NOT NULL DEFAULT 0,
                completion_percent decimal(5,2) NOT NULL DEFAULT 0,
                current_primary_animal varchar(80) NULL,
                current_secondary_animal varchar(80) NULL,
                current_house varchar(80) NULL,
                confidence_label varchar(40) NULL,
                updown_index decimal(6,2) NOT NULL DEFAULT 0,
                latest_result_json longtext NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY profile_key (profile_key),
                KEY user_id (user_id),
                KEY current_primary_animal (current_primary_animal),
                KEY current_house (current_house),
                KEY updated_at (updated_at)
            ) $charset;",

            "CREATE TABLE {$tables['answers']} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                profile_id bigint(20) unsigned NOT NULL,
                question_id bigint(20) unsigned NOT NULL,
                question_key varchar(190) NOT NULL,
                answer_json longtext NOT NULL,
                dimension_delta_json longtext NULL,
                animal_delta_json longtext NULL,
                updown_delta_json longtext NULL,
                answered_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY profile_question (profile_id, question_id),
                KEY profile_id (profile_id),
                KEY question_id (question_id),
                KEY answered_at (answered_at)
            ) $charset;",

            "CREATE TABLE {$tables['snapshots']} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                profile_id bigint(20) unsigned NOT NULL,
                total_answered int(11) NOT NULL DEFAULT 0,
                primary_animal varchar(80) NULL,
                secondary_animal varchar(80) NULL,
                house_key varchar(80) NULL,
                updown_index decimal(6,2) NOT NULL DEFAULT 0,
                result_json longtext NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY (id),
                KEY profile_id (profile_id),
                KEY primary_animal (primary_animal),
                KEY created_at (created_at)
            ) $charset;",
        );
    }
}
