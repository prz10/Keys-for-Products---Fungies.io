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
 * Requires at least: 6.3
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define( 'KSFP_KEYS_FOR_PRODUCTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KSFP_KEYS_FOR_PRODUCTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'KSFP_KEYS_FOR_PRODUCTS_ASSETS_URL', KSFP_KEYS_FOR_PRODUCTS_PLUGIN_URL . 'public/' );
define( 'KSFP_KEYS_FOR_PRODUCTS_IMAGES_URL', KSFP_KEYS_FOR_PRODUCTS_ASSETS_URL . 'images/' );

add_action( 'init', 'ksfp_init_keys_for_wordpress_woocommerce' );

function ksfp_activate_plugin_keys_for_wp_woo_fungies() {
	add_option( 'ksfp-keys-for-wp-woo-version', 'free' );
}
register_activation_hook( __FILE__, 'ksfp_activate_plugin_keys_for_wp_woo_fungies' );

function ksfp_init_keys_for_wordpress_woocommerce() {
	load_plugin_textdomain( 'keys-for-wp-woo-fungies', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	load_textdomain( 'keys-for-wp-woo-fungies', KSFP_KEYS_FOR_PRODUCTS_PLUGIN_PATH . 'languages/keys-for-wp-woo-fungies-en_US.mo' );
}

function is_plugin_version_paid() {
	$version = get_option( 'ksfp-keys-for-wp-woo-version' );
	return ( $version === 'paid' );
}

function ksfp_check_woocommerce_active() {
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		add_action( 'admin_notices', 'ksfp_woocommerce_missing_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_GET['activate'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
			unset( $_GET['activate'] );
		}
	}
}
add_action( 'admin_init', 'ksfp_check_woocommerce_active' );

function ksfp_woocommerce_missing_notice() {
	echo '<div class="error"><p>' . esc_html__( 'WooCommerce jest wymagany dla tego pluginu!', 'keys-for-wp-woo-fungies' ) . '</p></div>';
}

function ksfp_register_admin_menu() {
	add_menu_page(
		'Keys for Products',
		'Keys for Products',
		'manage_options',
		'ksfp_keys-for-products',
		'ksfp_keys_for_products_page_callback',
		'dashicons-admin-generic',
		80
	);
}
add_action( 'admin_menu', 'ksfp_register_admin_menu' );

function ksfp_keys_for_products_page_callback() {
	if ( is_plugin_version_paid() ) {
		include_once plugin_dir_path( __FILE__ ) . 'templates/paid-version-template.php';
	} else {
		include_once plugin_dir_path( __FILE__ ) . 'templates/free-version-template.php';
	}
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-ksfp-keys-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ksfp-product-keys-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ksfp-order-keys-manager.php';

function ksfp_keys_for_woocommerce_init() {
	$keys_manager         = new Ksfp_Keys_Manager();
	$product_keys_manager = new Ksfp_Product_Keys_Manager();
	$order_keys_manager   = new Ksfp_Order_Keys_Manager();
}
add_action( 'plugins_loaded', 'ksfp_keys_for_woocommerce_init' );
