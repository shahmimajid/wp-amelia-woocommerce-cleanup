<?php

class AWC_Cron_Manager {

    public static function init() {

        add_filter('cron_schedules', [self::class, 'schedule']);
        add_action('init', [self::class, 'register']);

    }

    public static function schedule($schedules) {

        $schedules['awc_every_5_minutes'] = [
            'interval' => 300,
            'display' => 'Every 5 Minutes'
        ];

        return $schedules;

    }

    public static function register() {

        if (!wp_next_scheduled('awc_cleanup_cron')) {

            wp_schedule_event(
                time(),
                'awc_every_5_minutes',
                'awc_cleanup_cron'
            );

        }

    }

}