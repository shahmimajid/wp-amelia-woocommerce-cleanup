<?php

class AWC_Cron_Manager {

    public static function init() {

        add_filter('cron_schedules', [self::class, 'register_interval']);
        add_action('init', [self::class, 'schedule_cron']);

    }

    public static function register_interval($schedules) {

        $schedules['awc_5_minutes'] = [
            'interval' => 300,
            'display'  => 'Every 5 Minutes'
        ];

        return $schedules;

    }

    public static function schedule_cron() {

        if (!wp_next_scheduled('awc_cleanup_cron')) {

            wp_schedule_event(
                time(),
                'awc_5_minutes',
                'awc_cleanup_cron'
            );

        }

    }

}