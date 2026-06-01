<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Assets
{
    public static function register_assets()
    {
        wp_register_style('acb-public', ACB_URL . 'assets/css/public.css', array(), ACB_VERSION);
        wp_register_style('acb-admin', ACB_URL . 'assets/css/admin.css', array(), ACB_VERSION);
        wp_register_script('acb-admin', ACB_URL . 'assets/js/admin.js', array(), ACB_VERSION, true);
    }

    public static function enqueue_public(array $settings = array())
    {
        wp_enqueue_style('acb-public');

        $custom_css = trim((string) ($settings['custom_css'] ?? ''));
        if ('' !== $custom_css) {
            wp_add_inline_style('acb-public', $custom_css);
        }
    }

    public static function wrapper_attributes(array $settings = array(), $context = 'assessment')
    {
        $classes = array('acb-shell', 'acb-shell--' . sanitize_html_class($context));
        if (!empty($settings['inherit_theme_styles'])) {
            $classes[] = 'acb-shell--theme';
        }
        if (!empty($settings['panel_shadow']) && 'none' !== $settings['panel_shadow']) {
            $classes[] = 'acb-shell--shadow-' . sanitize_html_class($settings['panel_shadow']);
        }

        $classes = apply_filters('acb_wrapper_classes', $classes, $settings, $context);
        $style = self::style_variables($settings);

        return sprintf(
            'class="%s"%s',
            esc_attr(implode(' ', array_unique(array_filter($classes)))),
            $style ? ' style="' . esc_attr($style) . '"' : ''
        );
    }

    public static function style_variables(array $settings = array())
    {
        $vars = array();
        $map = array(
            'accent_color' => '--acb-accent',
            'accent_color_secondary' => '--acb-accent-alt',
            'surface_color' => '--acb-surface',
            'panel_color' => '--acb-panel-bg',
            'text_color' => '--acb-text',
            'muted_color' => '--acb-muted',
        );

        foreach ($map as $option_key => $css_var) {
            $value = trim((string) ($settings[$option_key] ?? ''));
            if ('' !== $value) {
                $vars[] = $css_var . ':' . sanitize_text_field($value);
            }
        }

        $radius = absint($settings['border_radius'] ?? 0);
        if ($radius > 0) {
            $vars[] = '--acb-radius:' . $radius . 'px';
        }

        $shadow_value = self::shadow_value($settings['panel_shadow'] ?? 'medium');
        if ($shadow_value) {
            $vars[] = '--acb-shadow:' . $shadow_value;
        }

        return implode(';', $vars);
    }

    public static function animal_icon_url($animal_key)
    {
        $animal_key = sanitize_key($animal_key);
        $path = ACB_PATH . 'assets/images/animals/' . $animal_key . '.webp';
        $url = ACB_URL . 'assets/images/animals/' . $animal_key . '.webp';

        if (!file_exists($path)) {
            $url = '';
        }

        return apply_filters('acb_animal_icon_url', $url, $animal_key);
    }

    private static function shadow_value($shadow)
    {
        switch (sanitize_key($shadow)) {
            case 'small':
                return '0 10px 24px rgba(15, 23, 42, 0.08)';
            case 'large':
                return '0 20px 48px rgba(15, 23, 42, 0.16)';
            case 'none':
                return 'none';
            case 'medium':
            default:
                return '0 16px 36px rgba(15, 23, 42, 0.12)';
        }
    }
}
