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

        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        global $wpdb;

        $payments = $wpdb->prefix . 'amelia_payments';
        $bookings = $wpdb->prefix . 'amelia_customer_bookings';

        $booking_ids = self::get_booking_ids_for_order($order, $payments);

        AWC_Logger::debug(
            'Order ' . $order_id . ' matched Amelia booking IDs: ' .
            wp_json_encode($booking_ids)
        );

        if (!$booking_ids) {

            AWC_Logger::log(
                "No Amelia booking found for cancelled order {$order_id}"
            );

            return;

        }

        foreach ($booking_ids as $booking_id) {

            $updated = $wpdb->update(
                $bookings,
                ['status' => 'rejected'],
                ['id' => $booking_id],
                ['%s'],
                ['%d']
            );

            if ($updated !== false) {

                AWC_Logger::log(
                    "Rejected Amelia booking {$booking_id} for order {$order_id}"
                );

            }

        }

    }

    private static function get_booking_ids_for_order($order, $payments_table) {

        global $wpdb;

        $order_id      = intval($order->get_id());
        $parent_id     = intval($order->get_parent_id());
        $order_number  = trim((string) $order->get_order_number());
        $order_number_hash = $order_number ? '#' . $order_number : '';

        $candidates = array_filter(
            array_unique([
                (string) $order_id,
                $parent_id ? (string) $parent_id : '',
                $order_number,
                $order_number_hash
            ])
        );

        $child_order_ids = wc_get_orders([
            'parent' => $order_id,
            'return' => 'ids',
            'limit'  => -1
        ]);

        foreach ($child_order_ids as $child_order_id) {
            $candidates[] = (string) intval($child_order_id);
        }

        $candidates = array_values(array_unique(array_filter($candidates)));

        AWC_Logger::debug(
            'Order ' . $order_id . ' lookup candidates: ' .
            wp_json_encode($candidates)
        );

        if (!$candidates) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($candidates), '%s'));

        $query = $wpdb->prepare(
            "
            SELECT DISTINCT bookingId
            FROM {$payments_table}
            WHERE orderId IN ({$placeholders})
            AND bookingId IS NOT NULL
            ",
            ...$candidates
        );

        $booking_ids = $wpdb->get_col($query);

        return array_map('intval', array_filter($booking_ids));

    }

}
