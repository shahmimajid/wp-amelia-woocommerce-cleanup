<?php

class AWC_Settings_Page {

    public static function init() {

        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_init', [self::class, 'register']);

    }

    public static function menu() {

        add_submenu_page(
            'woocommerce',
            'Booking Cleanup',
            'Booking Cleanup',
            'manage_woocommerce',
            'awc-cleanup',
            [self::class, 'render']
        );

    }

    public static function register() {

        register_setting(
            'awc_cleanup_group',
            'awc_cleanup_settings'
        );

    }

    public static function render() {

        include AWC_PLUGIN_PATH .
            'admin/views/settings-page.php';

    }

}