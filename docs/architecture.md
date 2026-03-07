# Architecture

This plugin synchronizes booking state between:

- WooCommerce
- Amelia Booking

## Order Lifecycle

pending → processing → completed

If pending exceeds timeout:

pending → cancelled

## Booking Lifecycle

pending → approved

If order cancelled:

pending → rejected