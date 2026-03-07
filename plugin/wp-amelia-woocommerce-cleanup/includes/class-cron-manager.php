<?php

class AWC_Cron_Manager {

    public static function init() {

        add_filter('cron_schedules', [self::class, 'schedule']);
        add_action('init', [self::class, 'register']);
        add_action(
            'update_option_awc_cleanup_settings',
            [self::class, 'settings_updated'],
            10,
            2
        );

    }

    public static function schedule($schedules) {

        $minutes = self::get_interval_minutes();
        $key     = self::get_schedule_key($minutes);

        $schedules[$key] = [
            'interval' => $minutes * 60,
            'display'  => "Every {$minutes} Minutes"
        ];

        return $schedules;

    }

    public static function register() {

        $schedule = self::get_schedule_key(
            self::get_interval_minutes()
        );

        $event = function_exists('wp_get_scheduled_event')
            ? wp_get_scheduled_event('awc_cleanup_cron')
            : false;

        if (!$event) {

            wp_schedule_event(
                time() + 60,
                $schedule,
                'awc_cleanup_cron'
            );

            return;

        }

        if ($event->schedule !== $schedule) {

            wp_clear_scheduled_hook('awc_cleanup_cron');

            wp_schedule_event(
                time() + 60,
                $schedule,
                'awc_cleanup_cron'
            );

        }

    }

    public static function settings_updated($old_value, $value) {

        $old_minutes = self::normalize_minutes(
            $old_value['cron_interval'] ?? 10
        );

        $new_minutes = self::normalize_minutes(
            $value['cron_interval'] ?? 10
        );

        if ($old_minutes !== $new_minutes) {
            self::register();
        }

    }

    private static function get_interval_minutes() {

        $settings = get_option('awc_cleanup_settings');

        return self::normalize_minutes(
            $settings['cron_interval'] ?? 10
        );

    }

    private static function normalize_minutes($minutes) {

        return max(1, intval($minutes));

    }

    private static function get_schedule_key($minutes) {

        return 'awc_every_' . self::normalize_minutes($minutes) . '_minutes';

    }

}
