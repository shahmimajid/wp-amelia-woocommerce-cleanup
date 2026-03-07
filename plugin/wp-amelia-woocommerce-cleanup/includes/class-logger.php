<?php

class AWC_Logger {

    public static function log($message) {

        if (!self::should_log()) {
            return;
        }

        error_log('[AWC] ' . $message);

    }

    public static function debug($message) {

        if (!self::is_debug_mode()) {
            return;
        }

        error_log('[AWC][DEBUG] ' . $message);

    }

    private static function should_log() {

        return self::is_debug_mode() ||
            (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG);

    }

    private static function is_debug_mode() {

        $settings = get_option('awc_cleanup_settings');

        return !empty($settings['debug_mode']);

    }

}
