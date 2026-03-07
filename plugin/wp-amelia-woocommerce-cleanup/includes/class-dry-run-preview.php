<?php

if (!defined('ABSPATH')) {
    exit;
}

class AWC_Dry_Run_Preview {

    public static function init() {

        add_action(
            'wp_ajax_awc_dry_run_preview',
            [self::class, 'preview']
        );

    }

    public static function preview() {

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied');
        }

        if (!function_exists('wc_get_orders')) {
            wp_send_json_error('WooCommerce not available');
        }

        $settings = get_option('awc_cleanup_settings');

        $timeout = intval($settings['timeout'] ?? 30);
        $limit   = intval($settings['limit'] ?? 50);

        $cutoff = time() - ($timeout * 60);

        /*
        |--------------------------------------------------------------------------
        | Fetch candidate orders
        |--------------------------------------------------------------------------
        */

        $order_ids = wc_get_orders([
            'status'       => 'pending',
            'return'       => 'ids',
            'limit'        => $limit,
            'date_created' => '<' . $cutoff
        ]);

        if (!$order_ids) {
            wp_send_json_success([]);
        }

        global $wpdb;

        $payments_table   = $wpdb->prefix . 'amelia_payments';
        $bookings_table   = $wpdb->prefix . 'amelia_customer_bookings';
        $appointments_tbl = $wpdb->prefix . 'amelia_appointments';

        $results = [];

        foreach ($order_ids as $order_id) {

            $order = wc_get_order($order_id);

            if (!$order) {
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

            $created = $order->get_date_created();

            if (!$created) {
                continue;
            }

            $age_minutes = round(
                (time() - $created->getTimestamp()) / 60,
                1
            );

            $customer_email = $order->get_billing_email();

            /*
            |--------------------------------------------------------------------------
            | Fetch Amelia booking info
            |--------------------------------------------------------------------------
            */

            $appointment_time = '';

            $booking_id = $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT bookingId
                    FROM $payments_table
                    WHERE orderId = %d
                    LIMIT 1
                    ",
                    $order_id
                )
            );

            if ($booking_id) {

                $appointment_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "
                        SELECT appointmentId
                        FROM $bookings_table
                        WHERE id = %d
                        LIMIT 1
                        ",
                        $booking_id
                    )
                );

                if ($appointment_id) {

                    $appointment_time = $wpdb->get_var(
                        $wpdb->prepare(
                            "
                            SELECT bookingStart
                            FROM $appointments_tbl
                            WHERE id = %d
                            LIMIT 1
                            ",
                            $appointment_id
                        )
                    );

                }

            }

            $results[] = [
                'id'        => $order_id,
                'status'    => $order->get_status(),
                'age'       => $age_minutes,
                'email'     => $customer_email,
                'booking'   => $appointment_time
            ];

        }

        wp_send_json_success($results);

    }

}