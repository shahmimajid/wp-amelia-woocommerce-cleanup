<?php

class AWC_Logger {

    private const WC_LOG_SOURCE = 'awc-cleanup';

    public static function log($message) {

        if (!self::should_log()) {
            return;
        }

        self::write('info', $message);

    }

    public static function debug($message) {

        if (!self::is_debug_mode()) {
            return;
        }

        self::write('debug', $message);

    }

    public static function get_log_file_path() {

        if (function_exists('wc_get_log_file_path')) {
            return wc_get_log_file_path(self::WC_LOG_SOURCE);
        }

        $upload_dir = wp_upload_dir();

        if (empty($upload_dir['basedir'])) {
            return '';
        }

        return trailingslashit($upload_dir['basedir']) .
            'wc-logs/' . self::WC_LOG_SOURCE . '-' . gmdate('Y-m-d') . '.log';

    }

    public static function get_log_source() {

        return self::WC_LOG_SOURCE;

    }

    private static function should_log() {

        return self::is_debug_mode() ||
            (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG);

    }

    private static function is_debug_mode() {

        $settings = get_option('awc_cleanup_settings');

        return !empty($settings['debug_mode']);

    }

    private static function write($level, $message) {

        $context = ['source' => self::WC_LOG_SOURCE];

        if (function_exists('wc_get_logger')) {

            $logger = wc_get_logger();

            if ($level === 'debug') {
                $logger->debug($message, $context);
            } else {
                $logger->info($message, $context);
            }

            return;

        }

        $tag = $level === 'debug' ? 'AWC][DEBUG' : 'AWC';

        error_log(
            '[' . gmdate('Y-m-d H:i:s') . ' UTC]' .
            '[' . $tag . '] ' .
            $message
        );

    }

}
