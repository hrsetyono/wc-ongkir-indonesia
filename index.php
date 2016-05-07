<?php
/*
Plugin Name: WooCommerce Indonesia Shipping
Description: WooCommerce FREE Shipping plugin for JNE, TIKI, or POS. Requires purchase from RajaOngkir.com.
Plugin URI: http://github.com/hrsetyono/wc-indo-shipping
Author: The Syne Studio
Author URI: http://thesyne.com/
Version: 0.2.0
*/

// check if WooCommerce active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')) )) {
  return false;
}

// constant and import
define('WCIS_PLUGIN_DIR', plugins_url('', __FILE__) );
require_once 'lib/all.php';

// if enabled
$wcis_settings = get_option('woocommerce_wcis_settings');
if(array_key_exists('enabled', $wcis_settings) && $wcis_settings['enabled'] === 'yes') {
  // AJAX.php
  add_action('wp_ajax_wcis_get_cities', array('WCIS_Ajax', 'get_cities') );
  add_action('wp_ajax_nopriv_wcis_get_cities', array('WCIS_Ajax', 'get_cities') );

  add_action('wp_ajax_wcis_get_districts', array('WCIS_Ajax', 'get_districts') );
  add_action('wp_ajax_nopriv_wcis_get_districts', array('WCIS_Ajax', 'get_districts') );

  // CHECKOUT.php
  add_filter('woocommerce_checkout_fields', array('WCIS_Checkout', 'reorder_fields') );
  add_action('woocommerce_checkout_update_user_meta', array('WCIS_Checkout', 'update_user_meta'), 99, 2);
  add_action('woocommerce_checkout_update_order_meta', array('WCIS_Checkout', 'update_order_meta'), 99, 2);

  add_filter('woocommerce_cart_shipping_packages', array('WCIS_Checkout', 'parse_shipping_package') );

  // TEMPLATE.php
  add_action('wp_enqueue_scripts', 'wcis_enqueue_scripts', 999);
  add_action('wp_footer', 'wcis_field_template');

  //
  add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
  add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
}

add_action('woocommerce_shipping_init', 'wcis_init');
add_filter('woocommerce_shipping_methods', 'wcis_add_method');

/////

/*
  Initiate WC Shipping
*/
function wcis_init() {
  require_once('lib/wcis-main.php');
}

/*
  Add our custom Shipping method
*/
function wcis_add_method($methods) {
	$methods[] = 'WCIS_Method';
	return $methods;
}
