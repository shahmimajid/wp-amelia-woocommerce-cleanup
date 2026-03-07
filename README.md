# WP Amelia WooCommerce Cleanup

Automatically cancels abandoned WooCommerce orders and releases Amelia booking slots.

## Features

- Dry-run mode
- Deposit-safe cancellation
- Amelia booking synchronization
- Logging for debugging
- Configurable cron interval from plugin settings

## Workflow

1. Customer selects booking slot
2. Amelia creates pending booking
3. WooCommerce order created
4. Customer abandons payment
5. After timeout:
   - Order cancelled
   - Amelia booking rejected
   - Slot released

## Configuration

In WooCommerce → Booking Cleanup settings:
- Enable cleanup
- Dry run mode
- Timeout (minutes)
- Limit per run
- Cron interval (minutes, default 10)
- Debug mode (verbose logs for troubleshooting)

## Installation

Upload plugin folder to:
`wp-content/plugins/`

Then activate in WordPress Admin.

## Versioning

To keep plugin version aligned with a release tag, run:

```bash
./scripts/sync-version-with-tag.sh v1.3.0
```

This updates the plugin header version, `AWC_PLUGIN_VERSION`, and creates a changelog section when missing.
