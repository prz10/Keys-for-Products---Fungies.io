<?php
/**
 * Plugin Name: Keys for Products - Fungies.io
 * Plugin URI: hhttps://fungies.io/
 * Description: Managing game keys that will be connected to the product in WooCommerce.
 * Version: 1.0.0
 * Author: Kamil Perzowski - Fungies.io
 * Author URI: https://fungies.io/
 * License: GPLv3
 * Text Domain: keys-for-wp-woo-fungies
 * Domain Path: /languages/
 * Requires at least: 6.0 
 * Requires PHP: 7.4
 */

defined('ABSPATH') or die('No script kiddies please!');
define('KEYS_FOR_PRODUCTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KEYS_FOR_PRODUCTS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KEYS_FOR_PRODUCTS_ASSETS_URL', KEYS_FOR_PRODUCTS_PLUGIN_URL . 'public/');
define('KEYS_FOR_PRODUCTS_IMAGES_URL', KEYS_FOR_PRODUCTS_ASSETS_URL . 'images/');

add_action('init', 'initKeysForWordpressWoocommerce');

function activate_plugin_keys_for_wp_woo_fungies() {
    add_option('keys-for-wp-woo-version', 'free');
}
register_activation_hook(__FILE__, 'activate_plugin_keys_for_wp_woo_fungies');

function initKeysForWordpressWoocommerce(){
    load_plugin_textdomain('keys-for-wp-woo-fungies', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    load_textdomain('keys-for-wp-woo-fungies', KEYS_FOR_PRODUCTS_PLUGIN_PATH.'languages/keys-for-wp-woo-fungies-en_US.mo');
}

function is_plugin_version_paid() {
    $version = get_option('keys-for-wp-woo-version');
    return ($version == 'paid');
}

function check_woocommerce_active() {
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        add_action('admin_notices', 'woocommerce_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}
add_action('admin_init', 'check_woocommerce_active');

function woocommerce_missing_notice(){
    echo '<div class="error"><p>' . esc_html__('WooCommerce jest wymagany dla tego pluginu!', 'keys-for-wp-woo-fungies') . '</p></div>';
}

function register_admin_menu() {
    add_menu_page(
        'Keys for Products',
        'Keys for Products',
        'manage_options',
        'ksfp_keys-for-products',
        'keys_for_products_page_callback',
        'dashicons-admin-generic',
        80
    );
}
add_action('admin_menu', 'register_admin_menu');

function keys_for_products_page_callback() {
    if (is_plugin_version_paid()) {
        include_once plugin_dir_path(__FILE__) . 'templates/paid-version-template.php';
    } else {
        include_once plugin_dir_path(__FILE__) . 'templates/free-version-template.php';
    }
}

include_once plugin_dir_path(__FILE__) . 'includes/class-keys-manager.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-product-keys-manager.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-order-keys-manager.php';

function keys_for_woocommerce_init() {
	$keys_manager = new Keys_Manager();
	$product_keys_manager = new Product_Keys_Manager();
	$order_keys_manager = new Order_Keys_Manager();
}
add_action('plugins_loaded', 'keys_for_woocommerce_init');