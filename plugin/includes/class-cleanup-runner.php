<?php

class AWC_Cleanup_Runner {

    public static function init() {

        add_action('awc_cleanup_cron', [self::class, 'run']);

    }

    public static function run() {

        $settings = get_option('awc_cleanup_settings');

        if (empty($settings['enabled'])) {
            return;
        }

        $timeout = intval($settings['timeout'] ?? 30) * 60;
        $dry_run = !empty($settings['dry_run']);

        $orders = wc_get_orders([
            'status' => 'pending',
            'limit' => -1,
            'type' => 'shop_order'
        ]);

        foreach ($orders as $order) {

            if ($order->get_type() === 'wcdp_payment') {
                continue;
            }

            if ($order->get_parent_id()) {
                continue;
            }

            $created = $order->get_date_created();

            if (!$created) {
                continue;
            }

            $age = time() - $created->getTimestamp();

            if ($age < $timeout) {
                continue;
            }

            $order_id = $order->get_id();

            if ($dry_run) {

                AWC_Logger::log(
                    "DRY RUN: Order #$order_id detected"
                );

                continue;

            }

            $order->update_status(
                'cancelled',
                'Cancelled due to abandoned checkout.'
            );

            AWC_Logger::log("Cancelled order #$order_id");

        }

    }

}