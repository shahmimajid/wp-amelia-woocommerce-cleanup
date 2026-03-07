<?php
/*
Plugin Name: WP Amelia WooCommerce Cleanup
Description: Automatically cancel abandoned WooCommerce orders and release Amelia booking slots.
Version: 1.2.0
Author: ShahmiMajid
License: GPL-2.0-or-later
*/

if (!defined('ABSPATH')) {
    exit;
}

define('AWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AWC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once AWC_PLUGIN_PATH . 'includes/class-logger.php';
require_once AWC_PLUGIN_PATH . 'includes/class-cron-manager.php';
require_once AWC_PLUGIN_PATH . 'includes/class-cleanup-runner.php';
require_once AWC_PLUGIN_PATH . 'includes/class-amelia-sync.php';
require_once AWC_PLUGIN_PATH . 'admin/class-settings-page.php';
require_once AWC_PLUGIN_PATH . 'includes/class-dry-run-preview.php';

AWC_Dry_Run_Preview::init();

/*
|--------------------------------------------------------------------------
| GitHub Auto Update (disable during development)
|--------------------------------------------------------------------------
*/

// require_once AWC_PLUGIN_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';

// use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// $updateChecker = PucFactory::buildUpdateChecker(
//     'https://github.com/shahmimajid/wp-amelia-woocommerce-cleanup',
//     __FILE__,
//     'wp-amelia-woocommerce-cleanup'
// );

// $updateChecker->setBranch('main');

/*
|--------------------------------------------------------------------------
| Init
|--------------------------------------------------------------------------
*/

AWC_Cron_Manager::init();
AWC_Cleanup_Runner::init();
AWC_Amelia_Sync::init();
AWC_Settings_Page::init();