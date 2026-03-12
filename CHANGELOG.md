# Changelog

## 1.5.0

- Removed the guard in `AWC_Cleanup_Runner` that skipped orders with amelia_booking_id so cron can cancel eligible Amelia-linked pending orders.

- Restored and implemented `AWC_Amelia_Sync::get_booking_ids_for_order()` which looks up amelia_payments.orderId, falls back to the `amelia_booking_id` order meta, normalizes/deduplicates IDs, and returns an array of booking IDs.

- Added debug logging for both matched booking IDs and the no-match path to improve diagnostics when mapping cancelled orders to Amelia bookings.
Kept dry_run behavior unchanged so simulations remain non-destructive.

## 1.3.0

- Added plugin-level Debug Mode setting in admin UI
- Added verbose debug logging for Amelia booking lookup candidates and matches
- Bumped plugin version metadata to 1.3.0

## 1.1.0

- Added configurable cleanup cron interval in plugin settings (default: 10 minutes)
- Added automatic cron rescheduling when interval setting changes
- Fixed dry-run checkbox persistence by normalizing settings on save
- Added release helper script to sync plugin version with git tag

## 1.0.0

Initial release

- Abandoned WooCommerce order detection
- Deposit-safe logic
- Amelia booking rejection
- Dry-run mode
