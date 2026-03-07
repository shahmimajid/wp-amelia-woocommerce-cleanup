<?php

class AWC_Settings_Page {

    public static function init() {

        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_init', [self::class, 'register']);
        add_action('admin_init', [self::class, 'ensure_defaults']);

    }

    /*
    |--------------------------------------------------------------------------
    | Admin Menu
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Register Settings
    |--------------------------------------------------------------------------
    */

    public static function register() {

        register_setting(
            'awc_cleanup_group',
            'awc_cleanup_settings',
            [
                'sanitize_callback' => [self::class, 'sanitize_settings']
            ]
        );

    }

    public static function sanitize_settings($input) {

        $input = is_array($input) ? $input : [];

        return [
            'enabled' => !empty($input['enabled']) ? 1 : 0,
            'dry_run' => !empty($input['dry_run']) ? 1 : 0,
            'timeout' => max(1, intval($input['timeout'] ?? 30)),
            'limit'   => max(1, intval($input['limit'] ?? 50))
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Default Settings (First Install)
    |--------------------------------------------------------------------------
    */

    public static function ensure_defaults() {

        $defaults = [
            'enabled' => 0,
            'dry_run' => 1,
            'timeout' => 30,
            'limit'   => 50
        ];

        $existing = get_option('awc_cleanup_settings');

        if (!$existing) {

            add_option(
                'awc_cleanup_settings',
                $defaults
            );

        } else {

            /*
            Ensure any missing keys are added
            when plugin updates introduce new settings
            */

            $updated = false;

            foreach ($defaults as $key => $value) {

                if (!isset($existing[$key])) {

                    $existing[$key] = $value;
                    $updated = true;

                }

            }

            if ($updated) {

                update_option(
                    'awc_cleanup_settings',
                    $existing
                );

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Render Settings Page
    |--------------------------------------------------------------------------
    */

    public static function render() {

        include AWC_PLUGIN_PATH .
        'admin/views/settings-page.php';

    }

}
