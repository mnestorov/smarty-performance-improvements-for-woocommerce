<?php
/**
 * Plugin Name:          SM - Performance Improvements for WooCommerce
 * Plugin URI:           https://github.com/mnestorov/smarty-performance-improvements-for-woocommerce
 * Description:          Enhance WooCommerce performance by disabling unnecessary features and improving speed. Includes a settings page for users to enable or disable specific tweaks.
 * Version:              1.0.0
 * Author:               Smarty Studio | Martin Nestorov
 * Author URI:           https://github.com/mnestorov
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          smarty-performance-improvements
 * Requires at least:    6.0
 * Requires PHP:         7.4
 * WC requires at least: 7.0
 * WC tested up to:      9.4
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

        // General optimizations
        if (!empty($options['disable_stripe_scripts'])) {
            add_action('wp_enqueue_scripts', 'smarty_disable_stripe_scripts', 20);
        }

        if (!empty($options['disable_background_image_regeneration'])) {
            add_filter('woocommerce_background_image_regeneration', '__return_false');
        }

        if (!empty($options['disable_password_strength_meter'])) {
            add_action('wp_print_scripts', 'smarty_deregister_woocommerce_scripts', 20);
        }

        if (!empty($options['increase_csv_batch_limit'])) {
            add_filter('woocommerce_product_export_batch_limit', 'smarty_increase_csv_batch_limit', 999);
        }

        if (!empty($options['disable_marketplace_suggestions'])) {
            add_filter('woocommerce_allow_marketplace_suggestions', '__return_false');
        }

        if (!empty($options['disable_woocommerce_com_notice'])) {
            add_filter('woocommerce_helper_suppress_admin_notices', 'smarty_disable_woocommerce_com_notice');
        }

        if (!empty($options['disable_lazy_loading'])) {
            add_filter('wp_lazy_loading_enabled', '__return_false');
        }

        if (!empty($options['disable_nocache_headers'])) {
            add_filter('woocommerce_enable_nocache_headers', '__return_false');
        }

        if (!empty($options['disable_payment_gateway_suggestions'])) {
            add_filter('woocommerce_admin_payment_gateway_suggestion_specs', '__return_empty_array');
        }

        if (!empty($options['clear_unused_cron_jobs'])) {
            add_action('init', 'smarty_clear_unused_cron_jobs');
        }

        // Admin-specific optimizations
        if (is_admin()) {
            if (!empty($options['disable_woocommerce_admin'])) {
                add_filter('woocommerce_admin_disabled', 'smarty_disable_woocommerce_admin');
                add_action('admin_menu', 'smarty_remove_woocommerce_admin_features', 99);
            }

            if (!empty($options['remove_my_account_order_total'])) {
                add_filter('woocommerce_my_account_my_orders_columns', 'smarty_remove_my_account_order_total', 10);
            }

            if (!empty($options['remove_processing_order_count'])) {
                add_filter('woocommerce_include_processing_order_count_in_menu', '__return_false');
            }

            if (!empty($options['remove_dashboard_widgets'])) {
                add_action('wp_dashboard_setup', 'smarty_remove_dashboard_widgets', 40);
            }

            if (!empty($options['disable_woocommerce_widgets'])) {
                add_action('widgets_init', 'smarty_disable_woocommerce_widgets', 15);
            }

            if (!empty($options['disable_setup_widget'])) {
                add_action('wp_dashboard_setup', 'smarty_disable_setup_widget', 40);
            }

            if (!empty($options['hide_marketplace_and_subscriptions'])) {
                add_action('admin_menu', 'smarty_hide_woocommerce_menus', 71);
            }

            if (!empty($options['disable_marketing_hub'])) {
                add_filter('woocommerce_admin_features', 'smarty_disable_marketing_hub');
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
        add_options_page(
            __('Performance Improvements Settings', 'smarty-performance-improvements'),
            __('Performance Improvements', 'smarty-performance-improvements'),
            'manage_options',
            'smarty_pi_settings',
            'smarty_settings_page_html'
        );
    }
    add_action('admin_menu', 'smarty_settings_page');
}

if (!function_exists('smarty_settings_page_html')) {
    /**
     * Render the settings page with all 15 settings and descriptions.
     */
    function smarty_settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('smarty_pi_settings')) {
            $options = array_map('intval', $_POST['smarty_pi_options'] ?? []);
            update_option(PI_OPTIONS_KEY, $options);

            echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'smarty-performance-improvements') . '</p></div>';
        }

        $options = get_option(PI_OPTIONS_KEY, []);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Performance Improvements | Settings', 'smarty-performance-improvements'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('smarty_pi_settings'); ?>
                <table class="form-table">
                    <?php
                    $settings = [
                        'disable_background_image_regeneration' => __('Disable background image regeneration to save resources.', 'smarty-performance-improvements'),
                        'disable_password_strength_meter' => __('Disable the password strength meter to improve page load times.', 'smarty-performance-improvements'),
                        'increase_csv_batch_limit' => __('Increase the batch limit for CSV product exports for faster data handling.', 'smarty-performance-improvements'),
                        'disable_marketplace_suggestions' => __('Remove WooCommerce marketplace suggestions from the admin area.', 'smarty-performance-improvements'),
                        'disable_lazy_loading' => __('Disable native lazy loading to improve image handling.', 'smarty-performance-improvements'),
                        'disable_nocache_headers' => __('Disable WooCommerce no-cache headers for better caching.', 'smarty-performance-improvements'),
                        'disable_woocommerce_admin' => __('Disable WooCommerce Admin, including Analytics tab, notification bar, and Home screen.', 'smarty-performance-improvements'),
                        'disable_payment_gateway_suggestions' => __('Remove payment gateway suggestions from the admin.', 'smarty-performance-improvements'),
                        'clear_unused_cron_jobs' => __('Clear unused WooCommerce tracker cron jobs.', 'smarty-performance-improvements'),
                        'remove_my_account_order_total' => __('Remove the order total column from My Account orders.', 'smarty-performance-improvements'),
                        'remove_processing_order_count' => __('Remove the processing order count from the admin menu.', 'smarty-performance-improvements'),
                        'remove_dashboard_widgets' => __('Remove unnecessary WooCommerce dashboard widgets.', 'smarty-performance-improvements'),
                        'disable_woocommerce_widgets' => __('Disable all WooCommerce widgets. Warning: Ensure none of these widgets are in use.', 'smarty-performance-improvements'),
                        'disable_setup_widget' => __('Disable the WooCommerce setup widget from the dashboard.', 'smarty-performance-improvements'),
                        'disable_woocommerce_com_notice' => __('Disable WooCommerce.com notice to connect your store for updates and support.', 'smarty-performance-improvements'),
                        'hide_marketplace_and_subscriptions' => __('Hide WooCommerce "Marketplace" and "My Subscriptions" submenus.', 'smarty-performance-improvements'),
                        'disable_marketing_hub' => __('Disable the WooCommerce Marketing Hub to declutter the admin area.', 'smarty-performance-improvements'),
                        'disable_stripe_scripts' => __('Disable Stripe Payment Request Button on product pages for faster loading.', 'smarty-performance-improvements'),
                    ];

                    foreach ($settings as $key => $description) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></th>
                            <td>
                                <input type="checkbox" name="smarty_pi_options[<?php echo esc_attr($key); ?>]" value="1" <?php checked(!empty($options[$key]), true); ?>>
                                <p class="description"><?php echo esc_html($description); ?></p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('smarty_disable_woocommerce_admin')) {
    /**
     * Disable WooCommerce Admin features.
     *
     * @return bool Always return true to disable WooCommerce Admin.
     */
    function smarty_disable_woocommerce_admin() {
        return true;
    }
}

if (!function_exists('smarty_remove_woocommerce_admin_features')) {
    /**
     * Remove WooCommerce Admin-specific menus and features.
     *
     * @return void
     */
    function smarty_remove_woocommerce_admin_features() {
        // Remove WooCommerce Analytics tab
        remove_menu_page('wc-admin&path=/analytics/overview');
        // Remove Home screen feature
        remove_menu_page('wc-admin');
        // Remove WooCommerce Admin notification bar
        remove_action('admin_notices', ['Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes', 'show_notes']);
    }
}

if (!function_exists('smarty_remove_dashboard_widgets')) {
    /**
     * Remove unnecessary WooCommerce dashboard widgets.
     *
     * @return void
     */
    function smarty_remove_dashboard_widgets() {
        remove_meta_box('woocommerce_dashboard_status', 'dashboard', 'normal'); // Remove WooCommerce Status widget
        remove_meta_box('woocommerce_dashboard_recent_reviews', 'dashboard', 'normal'); // Remove WooCommerce Recent Reviews widget
    }
}

if (!function_exists('smarty_disable_setup_widget')) {
    /**
     * Disable the setup widget.
     */
    function smarty_disable_setup_widget() {
        remove_meta_box('wc_admin_dashboard_setup', 'dashboard', 'normal');
    }
}

if (!function_exists('smarty_disable_woocommerce_widgets')) {
    /**
     * Disable all WooCommerce widgets.
     *
     * @return void
     */
    function smarty_disable_woocommerce_widgets() {
        if (function_exists('unregister_widget')) {
            unregister_widget('WC_Widget_Recent_Products');
            unregister_widget('WC_Widget_Featured_Products');
            unregister_widget('WC_Widget_Product_Categories');
            unregister_widget('WC_Widget_Product_Tag_Cloud');
            unregister_widget('WC_Widget_Cart');
            unregister_widget('WC_Widget_Layered_Nav');
            unregister_widget('WC_Widget_Layered_Nav_Filters');
            unregister_widget('WC_Widget_Price_Filter');
            unregister_widget('WC_Widget_Product_Search');
            unregister_widget('WC_Widget_Top_Rated_Products');
            unregister_widget('WC_Widget_Recent_Reviews');
            unregister_widget('WC_Widget_Recently_Viewed');
            unregister_widget('WC_Widget_Best_Sellers');
            unregister_widget('WC_Widget_On_Sale');
            unregister_widget('WC_Widget_Random_Products');
        }
    }
}

if (!function_exists('smarty_disable_woocommerce_com_notice')) {
    /**
     * Disable WooCommerce.com connect notice.
     *
     * @return bool Always return true to suppress the notice.
     */
    function smarty_disable_woocommerce_com_notice() {
        return true;
    }
}

if (!function_exists('smarty_hide_woocommerce_menus')) {
    /**
     * Hide "Marketplace" and "My Subscriptions" submenus.
     */
    function smarty_hide_woocommerce_menus() {
        remove_submenu_page('woocommerce', 'wc-addons');
        remove_submenu_page('woocommerce', 'wc-addons&section=helper');
    }
}

if (!function_exists('smarty_disable_marketing_hub')) {
    /**
     * Disable WooCommerce Marketing Hub.
     */
    function smarty_disable_marketing_hub($features) {
        $key = array_search('marketing', $features);
        if ($key !== false) {
            unset($features[$key]);
        }
        return $features;
    }
}

if (!function_exists('smarty_disable_stripe_scripts')) {
    /**
     * Disable unnecessary Stripe scripts on product pages.
     *
     * @return void
     */
    function smarty_disable_stripe_scripts() {
        if (is_product() && function_exists('wp_dequeue_script')) {
            wp_dequeue_script('stripe-payment-request');
            wp_dequeue_script('wc-stripe-payment-request');
        }
    }
}

if (!function_exists('smarty_deregister_woocommerce_scripts')) {
    /**
     * Deregister unnecessary scripts.
     */
    function smarty_deregister_woocommerce_scripts() {
        wp_dequeue_script('wc-password-strength-meter');
    }
}

if (!function_exists('smarty_increase_csv_batch_limit')) {
    /**
     * Increase CSV batch export limit.
     */
    function smarty_increase_csv_batch_limit() {
        return 5000;
    }
}

if (!function_exists('smarty_clear_unused_cron_jobs')) {
    /**
     * Clear unused WooCommerce tracker cron job.
     */
    function smarty_clear_unused_cron_jobs() {
        wp_clear_scheduled_hook('woocommerce_tracker_send_event');
    }
}