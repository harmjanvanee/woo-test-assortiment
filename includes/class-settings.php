<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Settings
{

    private static $instance = null;

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_filter('woocommerce_get_settings_pages', array($this, 'add_settings_page'));
    }

    public function add_settings_page($settings)
    {
        $settings[] = include WTA_PLUGIN_DIR . 'includes/class-settings-page.php';
        return $settings;
    }
}
