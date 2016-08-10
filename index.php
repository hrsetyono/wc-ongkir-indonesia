<?php
/*
Plugin Name: WooCommerce Indonesia Shipping
Description: WooCommerce FREE Shipping plugin for JNE, TIKI, or POS. Requires purchase from RajaOngkir.com.
Plugin URI: http://github.com/hrsetyono/wc-indo-shipping
Author: The Syne Studio
Author URI: http://thesyne.com/
*/

// check if WooCommerce active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')) )) {
  return false;
}

// constant and import
define('WCIS_PLUGIN_DIR', plugins_url('', __FILE__) );
require_once 'lib/all.php';

$wcis_settings = get_option('woocommerce_wcis_settings');
$wcis_enabled = isset($wcis_settings['enabled']) ? $wcis_settings['enabled'] : 'no';

// only run these code if enabled
if($wcis_enabled === 'yes') {
  $wcis_ajax = new WCIS_Ajax();
  $wcis_checkout = new WCIS_Checkout();

  $wcis_frontend = new WCIS_Frontend();

  // change default
  add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
  add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
}

// run this code even if disabled
add_action('woocommerce_shipping_init', 'wcis_init');
add_filter('woocommerce_shipping_methods', 'register_wcis_method');

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
function register_wcis_method($methods) {
	$methods['wcis'] = 'WCIS_Method';
	return $methods;
}
