<?php
/*
Plugin Name: WP Amelia WooCommerce Cleanup
Description: Automatically cancel abandoned WooCommerce orders and release Amelia booking slots.
Version: 1.5.0
Author: Fotolab
License: GPL-2.0-or-later
*/

if (!defined('ABSPATH')) {
    exit;
}

define('AWC_PLUGIN_VERSION', '1.5.0');
define('AWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AWC_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| Wait for WooCommerce
|--------------------------------------------------------------------------
*/

add_action('plugins_loaded', function () {

    if (!class_exists('WooCommerce')) {
        return;
    }

    require_once AWC_PLUGIN_PATH . 'includes/class-logger.php';
    require_once AWC_PLUGIN_PATH . 'includes/class-cron-manager.php';
    require_once AWC_PLUGIN_PATH . 'includes/class-cleanup-runner.php';
    require_once AWC_PLUGIN_PATH . 'includes/class-amelia-sync.php';
    require_once AWC_PLUGIN_PATH . 'includes/class-dry-run-preview.php';
    require_once AWC_PLUGIN_PATH . 'admin/class-settings-page.php';

    /*
    |--------------------------------------------------------------------------
    | Init components
    |--------------------------------------------------------------------------
    */

    AWC_Cron_Manager::init();
    AWC_Cleanup_Runner::init();
    AWC_Amelia_Sync::init();
    AWC_Dry_Run_Preview::init();

    if (is_admin()) {
        AWC_Settings_Page::init();
    }

});