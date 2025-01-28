# SM - Performance Improvements for WooCommerce

[![Licence](https://img.shields.io/badge/LICENSE-GPL2.0+-blue)](./LICENSE)

- **Developed by:** Martin Nestorov 
    - Explore more at [nestorov.dev](https://github.com/mnestorov)
- **Plugin URI:** https://github.com/mnestorov/smarty-performance-improvements-for-woocommerce

## Overview

The **Smarty Studio - Performance Improvements for WooCommerce** plugin is a lightweight solution to optimize your WooCommerce store by disabling unnecessary features and improving performance. With a user-friendly settings page, you can choose which performance tweaks to apply, ensuring faster load times and a better experience for store admins and customers.

## Features

- **User-friendly settings page**: Enable or disable specific optimizations.
- **Performance tweaks**:
  - Disable WooCommerce background image regeneration.
  - Remove password strength meter.
  - Increase CSV export batch limits.
  - Disable marketplace suggestions and admin notices.
  - Suppress WooCommerce no-cache headers.
  - Disable WooCommerce admin features.
- **Admin-specific tweaks**:
  - Remove unnecessary dashboard widgets.
  - Hide specific product columns in the admin product list.
  - Disable processing order counts in the admin menu.
  - Clear unused WooCommerce cron jobs.
- **Translation-ready**: Supports localization for global users.

## Installation

1. **Download the plugin**:
   - Clone the repository or download the zip file from [GitHub](https://github.com/mnestorov/smarty-performance-improvements-for-woocommerce).

2. **Install via WordPress**:
   - Go to `Plugins > Add New > Upload Plugin`.
   - Upload the zip file and click "Install Now".
   - Activate the plugin.

3. **Configure settings**:
   - Navigate to `Settings > Performance Improvements` to enable or disable specific tweaks.

## Usage

1. After installing and activating the plugin, go to the settings page under `Settings > Performance Improvements`.
2. Use the checkboxes to enable or disable individual performance improvements.
3. Save your settings, and the plugin will dynamically apply the changes to your WooCommerce store.

## Functions

- **General optimizations**:
  - `disable_background_image_regeneration`
  - `disable_password_strength_meter`
  - `increase_csv_batch_limit`
  - `disable_marketplace_suggestions`
  - `suppress_admin_notices`
  - `disable_lazy_loading`
  - `disable_nocache_headers`
  - `disable_admin`

- **Admin-specific tweaks**:
  - `remove_my_account_order_total`
  - `remove_processing_order_count`
  - `remove_dashboard_widgets`
  - `unset_product_list_columns`
  - `clear_unused_cron_jobs`

  Each function is modular and runs only if enabled in the settings page.

## Requirements

- **WordPress Version**: 6.0 or higher
- **PHP Version**: 7.4 or higher
- **WooCommerce Version**: 7.0 or higher
- **Tested Up To**:
  - WordPress: 6.3+
  - WooCommerce: 9.4+

## Changelog

For a detailed list of changes and updates made to this project, please refer to our [Changelog](./CHANGELOG.md).

## Contributing

Contributions are welcome. Please follow the WordPress coding standards and submit pull requests for any enhancements.

---

## License

This project is released under the [GPL-2.0+ License](http://www.gnu.org/licenses/gpl-2.0.txt).
