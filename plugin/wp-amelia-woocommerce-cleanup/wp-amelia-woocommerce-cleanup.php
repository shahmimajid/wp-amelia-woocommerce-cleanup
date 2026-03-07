<?php
/*
Plugin Name: WP Amelia WooCommerce Cleanup
Description: Automatically detect abandoned WooCommerce booking orders and optionally cancel them while syncing Amelia bookings.
Version: 1.0.0
Author: ShahmiMajid
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| SETTINGS
|--------------------------------------------------------------------------
*/

define('AMELIA_WC_CLEANUP_DRY_RUN', true);      // true = only log, no cancellation
define('AMELIA_WC_CLEANUP_TIMEOUT', 1800);     // 30 minutes


/*
|--------------------------------------------------------------------------
| CRON SCHEDULE
|--------------------------------------------------------------------------
*/

add_filter('cron_schedules', function ($schedules) {

    $schedules['amelia_wc_cleanup_5_minutes'] = [
        'interval' => 300,
        'display'  => 'Every 5 Minutes'
    ];

    return $schedules;
});

add_action('init', function () {

    if (!wp_next_scheduled('amelia_wc_cleanup_abandoned_orders')) {
        wp_schedule_event(time(), 'amelia_wc_cleanup_5_minutes', 'amelia_wc_cleanup_abandoned_orders');
    }

});


/*
|--------------------------------------------------------------------------
| MAIN CLEANUP
|--------------------------------------------------------------------------
*/

add_action('amelia_wc_cleanup_abandoned_orders', function () {

    if (!function_exists('wc_get_orders')) {
        return;
    }

    $orders = wc_get_orders([
        'status' => 'pending',
        'limit'  => -1,
        'type'   => 'shop_order'
    ]);

    if (!$orders) {
        return;
    }

    foreach ($orders as $order) {

        $order_id = $order->get_id();

        // Skip deposit payment child orders
        if ($order->get_type() === 'wcdp_payment') {
            continue;
        }

        // Skip child orders
        if ($order->get_parent_id()) {
            continue;
        }

        $created = $order->get_date_created();

        if (!$created) {
            continue;
        }

        $created_timestamp = $created->getTimestamp();
        $age = time() - $created_timestamp;

        if ($age < AMELIA_WC_CLEANUP_TIMEOUT) {
            continue;
        }

        $log = '[Amelia WC Cleanup] Order #' . $order_id .
            ' | age: ' . round($age/60,2) .
            ' minutes | status: ' . $order->get_status();

        if (AMELIA_WC_CLEANUP_DRY_RUN) {

            error_log('[DRY RUN] ' . $log);
            continue;

        }

        $order->update_status(
            'cancelled',
            'Automatically cancelled due to abandoned checkout.'
        );

        error_log('[CANCELLED] ' . $log);

    }

});


/*
|--------------------------------------------------------------------------
| SYNC AMELIA BOOKINGS
|--------------------------------------------------------------------------
*/

add_action('woocommerce_order_status_cancelled', function ($order_id) {

    if (AMELIA_WC_CLEANUP_DRY_RUN) {
        return;
    }

    global $wpdb;

    $payments_table = $wpdb->prefix . 'amelia_payments';
    $bookings_table = $wpdb->prefix . 'amelia_customer_bookings';

    $booking_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT bookingId FROM $payments_table WHERE orderId = %d",
            $order_id
        )
    );

    if (!$booking_ids) {
        return;
    }

    foreach ($booking_ids as $booking_id) {

        $wpdb->update(
            $bookings_table,
            ['status' => 'rejected'],
            ['id' => $booking_id],
            ['%s'],
            ['%d']
        );

        error_log(
            '[Amelia WC Cleanup] Rejected booking #' .
            $booking_id . ' for order #' . $order_id
        );

    }

});