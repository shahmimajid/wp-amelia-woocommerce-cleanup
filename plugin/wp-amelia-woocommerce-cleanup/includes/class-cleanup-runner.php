<?php

class AWC_Cleanup_Runner {

    public static function init() {

        add_action(
            'awc_cleanup_cron',
            [self::class, 'run']
        );

    }

    public static function run() {

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
        | Optimized query
        |--------------------------------------------------------------------------
        |
        | Only fetch order IDs older than timeout
        | Avoid loading thousands of orders into memory
        |
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
            | Ensure still pending
            |--------------------------------------------------------------------------
            */

            if ($order->get_status() !== 'pending') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip deposit child orders
            |--------------------------------------------------------------------------
            */

            if ($order->get_type() === 'wcdp_payment') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip child orders
            |--------------------------------------------------------------------------
            */

            if ($order->get_parent_id()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Payment gateway safeguard
            |--------------------------------------------------------------------------
            |
            | Prevent cancellation while customer is
            | still completing payment on gateway page
            |
            | Minimum 2 minute grace period
            |
            */

            $created = $order->get_date_created();

            if ($created) {

                $age_seconds = time() - $created->getTimestamp();

                if ($age_seconds < 120) {
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
                    "DRY RUN detected abandoned order #{$order_id}"
                );

                continue;

            }

            /*
            |--------------------------------------------------------------------------
            | Cancel order
            |--------------------------------------------------------------------------
            */

            $order->update_status(
                'cancelled',
                'Cancelled automatically due to abandoned checkout timeout.'
            );

            AWC_Logger::log(
                "Cancelled abandoned order #{$order_id}"
            );

        }

    }

}