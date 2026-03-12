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

        $order_id = $order->get_id();

        $booking_ids = $wpdb->get_col(
            $wpdb->prepare(
                "
                SELECT bookingId
                FROM $payments
                WHERE orderId = %d
                ",
                $order_id
            )
        );

        $booking_ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', (array) $booking_ids)
                )
            )
        );

        if ($booking_ids) {

            AWC_Logger::debug(
                'Matched Amelia booking IDs [' .
                implode(', ', $booking_ids) .
                "] for order #{$order_id} via amelia_payments.orderId"
            );

        } else {

            $meta_booking_id = intval($order->get_meta('amelia_booking_id'));

            if ($meta_booking_id > 0) {

                $booking_ids = [$meta_booking_id];

                AWC_Logger::debug(
                    "Matched Amelia booking ID {$meta_booking_id} for order #{$order_id} via order meta"
                );

            }

        }

        if (!$booking_ids) {
            AWC_Logger::debug(
                "No Amelia booking mapping found for order #{$order_id}"
            );
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

    private static function get_booking_ids_for_order($order, $payments_table) {

        global $wpdb;

        $order_id = $order->get_id();

        $booking_ids = $wpdb->get_col(
            $wpdb->prepare(
                "
                SELECT bookingId
                FROM $payments_table
                WHERE orderId = %d
                ",
                $order_id
            )
        );

        $booking_ids = array_values(
            array_unique(
                array_filter(
                    array_map('intval', (array) $booking_ids)
                )
            )
        );

        if ($booking_ids) {

            AWC_Logger::debug(
                'Matched Amelia booking IDs [' .
                implode(', ', $booking_ids) .
                "] for order #{$order_id} via amelia_payments.orderId"
            );

            return $booking_ids;

        }

        $meta_booking_id = intval($order->get_meta('amelia_booking_id'));

        if ($meta_booking_id > 0) {

            AWC_Logger::debug(
                "Matched Amelia booking ID {$meta_booking_id} for order #{$order_id} via order meta"
            );

            return [$meta_booking_id];

        }

        return [];

    }

}
