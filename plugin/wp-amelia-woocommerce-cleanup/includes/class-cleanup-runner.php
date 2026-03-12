<?php

class AWC_Cleanup_Runner {

    public static function init() {

        add_action(
            'awc_cleanup_cron',
            [self::class, 'run']
        );

    }

    public static function run() {

        /*
        |--------------------------------------------------------------------------
        | Only run via WP Cron
        |--------------------------------------------------------------------------
        */

        if (!wp_doing_cron()) {
            return;
        }

        if (!function_exists('wc_get_orders')) {
            return;
        }

        $settings = get_option('awc_cleanup_settings');

        if (empty($settings['enabled'])) {
            return;
        }

        $timeout_minutes = intval($settings['timeout'] ?? 30);
        $limit           = intval($settings['limit'] ?? 50);
        $dry_run         = !empty($settings['dry_run']);

        $cutoff_timestamp = time() - ($timeout_minutes * 60);

        /*
        |--------------------------------------------------------------------------
        | Fetch candidate orders (HPOS compatible)
        |--------------------------------------------------------------------------
        */

        $order_ids = wc_get_orders([
            'status'       => 'pending',
            'return'       => 'ids',
            'limit'        => $limit,
            'date_created' => '<' . $cutoff_timestamp
        ]);

        if (!$order_ids) {
            return;
        }

        foreach ($order_ids as $order_id) {

            $order = wc_get_order($order_id);

            if (!$order) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip WooCommerce Deposits parent orders
            |--------------------------------------------------------------------------
            */

            if ($order->get_meta('_wc_deposits_deposit_amount')) {

                AWC_Logger::debug(
                    "Skipping deposit order #{$order_id}"
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip deposit child orders
            |--------------------------------------------------------------------------
            */

            if ($order->get_type() === 'wcdp_payment') {

                AWC_Logger::debug(
                    "Skipping deposit child order #{$order_id}"
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip orders with children
            |--------------------------------------------------------------------------
            */

            $children = wc_get_orders([
                'parent' => $order_id,
                'limit'  => 1
            ]);

            if ($children) {

                AWC_Logger::debug(
                    "Skipping order #{$order_id} because it has children"
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Payment gateway grace period
            |--------------------------------------------------------------------------
            */

            $created = $order->get_date_created();

            if ($created) {

                $age_seconds = time() - $created->getTimestamp();

                if ($age_seconds < 120) {

                    AWC_Logger::debug(
                        "Skipping order #{$order_id} due to gateway grace period"
                    );

                    continue;
                }

            }

            /*
            |--------------------------------------------------------------------------
            | DRY RUN
            |--------------------------------------------------------------------------
            */

            if ($dry_run) {

                AWC_Logger::log(
                    "Dry Run: would cancel order #{$order_id}"
                );

                continue;

            }

            /*
            |--------------------------------------------------------------------------
            | Cancel abandoned order
            |--------------------------------------------------------------------------
            */

            $order->update_status(
                'cancelled',
                'Cancelled automatically due to abandoned checkout.'
            );

            AWC_Logger::log(
                "Cancelled abandoned order #{$order_id}"
            );

        }

    }

}
