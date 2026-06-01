<?php
/**
 * Plugin Name: American Civic Bestiary
 * Plugin URI: https://example.com/american-civic-bestiary
 * Description: Premium-ready civic-instinct profile engine for The American Civic Bestiary.
 * Version: 1.0.0
 * Author: American Civic Bestiary
 * Text Domain: american-civic-bestiary
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ACB_VERSION', '1.0.0');
define('ACB_FILE', __FILE__);
define('ACB_PATH', plugin_dir_path(__FILE__));
define('ACB_URL', plugin_dir_url(__FILE__));

require_once ACB_PATH . 'includes/class-acb-core-data.php';
require_once ACB_PATH . 'includes/class-acb-schema.php';
require_once ACB_PATH . 'includes/class-acb-activator.php';
require_once ACB_PATH . 'includes/class-acb-repository.php';
require_once ACB_PATH . 'includes/class-acb-scoring-engine.php';
require_once ACB_PATH . 'includes/class-acb-template.php';
require_once ACB_PATH . 'includes/class-acb-assets.php';
require_once ACB_PATH . 'includes/class-acb-shortcodes.php';
require_once ACB_PATH . 'includes/class-acb-admin.php';

final class ACB_Plugin
{
    private static $instance = null;
    private $repository;
    private $scoring_engine;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->repository = new ACB_Repository();
        $this->scoring_engine = new ACB_Scoring_Engine();
    }

    public function register_hooks()
    {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('plugins_loaded', array($this, 'maybe_upgrade'));
        add_action('init', array('ACB_Assets', 'register_assets'));

        $shortcodes = new ACB_Shortcodes($this->repository, $this->scoring_engine);
        $shortcodes->register_hooks();

        if (is_admin()) {
            $admin = new ACB_Admin($this->repository, $this->scoring_engine);
            $admin->register_hooks();
        }
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('american-civic-bestiary', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function maybe_upgrade()
    {
        $installed = get_option('acb_db_version');
        if ($installed !== ACB_VERSION) {
            ACB_Activator::activate(false);
        }
    }
}

register_activation_hook(__FILE__, array('ACB_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('ACB_Activator', 'deactivate'));

ACB_Plugin::instance()->register_hooks();
