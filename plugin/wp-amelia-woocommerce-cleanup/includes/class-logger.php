<?php

class AWC_Logger {

    public static function log($message) {

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[AWC] ' . $message);
        }

    }

}