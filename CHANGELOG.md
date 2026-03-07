# Changelog

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
