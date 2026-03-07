<?php

class AWC_Amelia_Sync {

    public static function init() {

        add_action(
            'woocommerce_order_status_cancelled',
            [self::class, 'reject']
        );

    }

    public static function reject($order_id) {

        $settings = get_option('awc_cleanup_settings');

        if (!empty($settings['dry_run'])) {
            return;
        }

        global $wpdb;

        $payments = $wpdb->prefix . 'amelia_payments';
        $bookings = $wpdb->prefix . 'amelia_customer_bookings';

        $booking_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT bookingId FROM $payments WHERE orderId = %d",
                $order_id
            )
        );

        foreach ($booking_ids as $booking_id) {

            $wpdb->update(
                $bookings,
                ['status' => 'rejected'],
                ['id' => $booking_id]
            );

            AWC_Logger::log(
                "Rejected Amelia booking $booking_id for order $order_id"
            );

        }

    }

}