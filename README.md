# WP Amelia WooCommerce Cleanup

Automatically cancels abandoned WooCommerce orders and releases Amelia booking slots.

## Features

- Dry-run mode
- Deposit-safe cancellation
- Amelia booking synchronization
- Logging for debugging

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

Inside plugin file:
AMELIA_WC_CLEANUP_DRY_RUN = true
AMELIA_WC_CLEANUP_TIMEOUT = 1800

## Installation

Upload plugin folder to:
`wp-content/plugins/`

Then activate in WordPress Admin.