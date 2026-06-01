<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Template
{
    public static function locate($template)
    {
        $template = ltrim((string) $template, '/');
        if ('' === $template) {
            return '';
        }

        $candidates = apply_filters('acb_template_candidates', array(
            'american-civic-bestiary/' . $template,
            $template,
        ), $template);

        foreach ((array) $candidates as $candidate) {
            $located = locate_template($candidate, false, false);
            if ($located) {
                return $located;
            }
        }

        $plugin_template = ACB_PATH . 'templates/' . $template;
        return file_exists($plugin_template) ? $plugin_template : '';
    }

    public static function render($template, array $args = array())
    {
        $path = self::locate($template);
        if (!$path) {
            return '';
        }

        $args = apply_filters('acb_template_args', $args, $template, $path);

        ob_start();
        extract($args, EXTR_SKIP);
        include $path;
        return ob_get_clean();
    }
}
