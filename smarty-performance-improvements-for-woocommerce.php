<?php
/**
 * Plugin Name:          SM - Performance Improvements for WooCommerce
 * Plugin URI:           https://github.com/mnestorov/smarty-performance-improvements-for-woocommerce
 * Description:          Enhance WooCommerce performance by disabling unnecessary features and improving speed. Includes a settings page for users to enable or disable specific tweaks, such as disabling background image regeneration, removing WooCommerce admin notices, and increasing CSV batch export limits.
 * Version:              1.0.0
 * Author:               Smarty Studio | Martin Nestorov
 * Author URI:           https://github.com/mnestorov
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          smarty-performance-improvements
 * Requires at least:    7.2
 * Requires PHP:         7.4
 * WC requires at least: 6.0
 * WC tested up to:      9.4
 * Requires Plugins:     woocommerce
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin option key
define('PI_OPTIONS_KEY', 'smarty_pi_options');

if (!function_exists('smarty_load_textdomain')) {
    /**
     * Load plugin text domain for translations.
     */
    function smarty_load_textdomain() {
        load_plugin_textdomain('smarty-performance-improvements', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    add_action('init', 'smarty_load_textdomain');
}

if (!function_exists('smarty_pi_init')) {
    /**
     * Initialize the plugin by hooking into WordPress.
     */
    function smarty_pi_init() {
        $options = get_option(PI_OPTIONS_KEY, []);

        // General tweaks
        if (!empty($options['disable_background_image_regeneration'])) {
            add_filter('woocommerce_background_image_regeneration', '__return_false');
        }

        if (!empty($options['disable_password_strength_meter']) && function_exists('smarty_deregister_woocommerce_scripts')) {
            add_action('wp_print_scripts', 'smarty_deregister_woocommerce_scripts', 20);
        }

        if (!empty($options['increase_csv_batch_limit']) && function_exists('smarty_increase_csv_batch_limit')) {
            add_filter('woocommerce_product_export_batch_limit', 'smarty_increase_csv_batch_limit', 999);
        }

        if (!empty($options['disable_marketplace_suggestions'])) {
            add_filter('woocommerce_allow_marketplace_suggestions', '__return_false');
        }

        if (!empty($options['suppress_admin_notices'])) {
            add_filter('woocommerce_helper_suppress_admin_notices', '__return_true');
        }

        if (!empty($options['disable_lazy_loading'])) {
            add_filter('wp_lazy_loading_enabled', '__return_false');
        }

        if (!empty($options['disable_nocache_headers'])) {
            add_filter('woocommerce_enable_nocache_headers', '__return_false');
        }

        if (!empty($options['disable_admin'])) {
            add_filter('woocommerce_admin_disabled', '__return_true');
        }

        if (!empty($options['clear_unused_cron_jobs']) && function_exists('smarty_clear_unused_cron_jobs')) {
            add_action('init', 'smarty_clear_unused_cron_jobs');
        }

        // Admin-specific optimizations
        if (is_admin()) {
            if (!empty($options['remove_my_account_order_total'])) {
                add_filter('woocommerce_my_account_my_orders_columns', 'smarty_remove_my_account_order_total', 10);
            }

            if (!empty($options['remove_processing_order_count'])) {
                add_filter('woocommerce_include_processing_order_count_in_menu', '__return_false');
            }

            if (!empty($options['remove_dashboard_widgets'])) {
                add_action('wp_dashboard_setup', 'smarty_remove_dashboard_widgets', 40);
            }

            if (!empty($options['unset_product_list_columns'])) {
                add_filter('manage_edit-product_columns', 'smarty_unset_product_list_columns');
            }
        }
    }
    add_action('plugins_loaded', 'smarty_pi_init');
}

if (!function_exists('smarty_settings_page')) {
    /**
     * Add the settings page to the WordPress admin menu.
     */
    function smarty_settings_page() {
        if (function_exists('add_options_page')) {
            add_options_page(
                __('Performance Improvements Settings', 'smarty-performance-improvements'),
                __('Performance Improvements', 'smarty-performance-improvements'),
                'manage_options',
                'smarty_pi_settings',
                'smarty_settings_page_html'
            );
        }
    }
    add_action('admin_menu', 'smarty_settings_page');
}

if (!function_exists('smarty_settings_page_html')) {
    /**
     * Render the settings page.
     */
    function smarty_settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('smarty_pi_settings')) {
            update_option(PI_OPTIONS_KEY, $_POST['smarty_pi_options']);
            echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'smarty-performance-improvements') . '</p></div>';
        }

        $options = get_option(PI_OPTIONS_KEY, []);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Performance Improvements | Settings', 'smarty-performance-improvements'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('smarty_pi_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Disable Background Image Regeneration', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[disable_background_image_regeneration]" value="1" <?php checked(!empty($options['disable_background_image_regeneration']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Disable Password Strength Meter', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[disable_password_strength_meter]" value="1" <?php checked(!empty($options['disable_password_strength_meter']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Increase CSV Batch Limit', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[increase_csv_batch_limit]" value="1" <?php checked(!empty($options['increase_csv_batch_limit']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Disable Marketplace Suggestions', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[disable_marketplace_suggestions]" value="1" <?php checked(!empty($options['disable_marketplace_suggestions']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Suppress Admin Notices', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[suppress_admin_notices]" value="1" <?php checked(!empty($options['suppress_admin_notices']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Disable Lazy Loading', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[disable_lazy_loading]" value="1" <?php checked(!empty($options['disable_lazy_loading']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Disable No-Cache Headers', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[disable_nocache_headers]" value="1" <?php checked(!empty($options['disable_nocache_headers']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Disable WooCommerce Admin', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[disable_admin]" value="1" <?php checked(!empty($options['disable_admin']), true); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Clear Unused Cron Jobs', 'smarty-performance-improvements'); ?></th>
                        <td>
                            <input type="checkbox" name="smarty_pi_options[clear_unused_cron_jobs]" value="1" <?php checked(!empty($options['clear_unused_cron_jobs']), true); ?>>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('smarty_deregister_woocommerce_scripts')) {
    /**
     * Deregister unnecessary scripts.
     *
     * @return void
     */
    function smarty_deregister_woocommerce_scripts() {
        if (function_exists('wp_dequeue_script')) {
            wp_dequeue_script('wc-password-strength-meter');
        }
    }
}

if (!function_exists('smarty_increase_csv_batch_limit')) {
    /**
     * Increase the CSV product exporter batch limit.
     *
     * @return int The new batch limit.
     */
    function smarty_increase_csv_batch_limit() {
        return 5000;
    }
}

if (!function_exists('smarty_clear_unused_cron_jobs')) {
    /**
     * Clear unused WooCommerce tracker cron job.
     *
     * @return void
     */
    function smarty_clear_unused_cron_jobs() {
        if (function_exists('wp_clear_scheduled_hook')) {
            wp_clear_scheduled_hook('woocommerce_tracker_send_event');
        }
    }
}