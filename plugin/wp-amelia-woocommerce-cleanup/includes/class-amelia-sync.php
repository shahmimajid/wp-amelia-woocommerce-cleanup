<?php

class AWC_Amelia_Sync {

    public static function init() {

        add_action(
            'woocommerce_order_status_cancelled',
            [self::class, 'reject']
        );

    }

    public static function reject($order_id) {

        /*
        |--------------------------------------------------------------------------
        | Do not run during admin page loads
        |--------------------------------------------------------------------------
        */

        if (is_admin() && !wp_doing_ajax()) {
            return;
        }

        $settings = get_option('awc_cleanup_settings');

        if (!empty($settings['dry_run'])) {
            return;
        }

        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Skip deposit orders
        |--------------------------------------------------------------------------
        */

        if ($order->get_meta('_wc_deposits_deposit_amount')) {
            return;
        }

        global $wpdb;

        $payments = $wpdb->prefix . 'amelia_payments';
        $bookings = $wpdb->prefix . 'amelia_customer_bookings';

        $booking_ids = self::get_booking_ids_for_order($order, $payments);

        if (!$booking_ids) {
            return;
        }

        foreach ($booking_ids as $booking_id) {

            $wpdb->update(
                $bookings,
                ['status' => 'rejected'],
                ['id' => $booking_id]
            );

            AWC_Logger::log(
                "Rejected Amelia booking {$booking_id} for order #{$order_id}"
            );

        }

    }

}