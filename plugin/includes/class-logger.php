<?php

class AWC_Logger {

    public static function log($message) {

        if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }

        error_log('[AWC] ' . $message);

    }

}