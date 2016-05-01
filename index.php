<?php
/*
Plugin Name: WooCommerce Indonesia Shipping
Description: WooCommerce FREE Shipping plugin for JNE, TIKI, or POS. Uses data from RajaOngkir.com.
Plugin URI: http://github.com/hrsetyono/wc-indo-shipping
Author: The Syne Studio
Author URI: http://thesyne.com/
Version: 0.1.0
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
  add_action('woocommerce_shipping_init', 'wcis_init');
  add_filter('woocommerce_shipping_methods', 'wcis_add_method');

  add_action('wp_ajax_wcis_get_city', 'wcis_ajax_get_city');

  // queue css and js
  add_action('wp_enqueue_scripts', 'wcis_enqueue_scripts', 999);

  // reorder checkout fields
  add_filter('woocommerce_checkout_fields', 'wcis_checkout_fields');
}

/////

/*
  Initiate WC Shipping
*/
function wcis_init() {
  require_once('lib/shipping.php');
}

/*
  Add our custom Shipping method
*/
function wcis_add_method($methods) {
	$methods[] = 'WCIS_Method';
	return $methods;
}

/*
  AJAX translate province code into City list.
*/
function wcis_ajax_get_city() {
	$code = $_GET['code'];
	$id = WCIS_Provinces::get_id($code);

  $settings = get_option('woocommerce_wcis_settings');

  $api = new WCIS_API($settings['key']);
  $response = $api->get_cities($id);
  echo json_encode($response);

	wp_die();
}

/*
  Reorder Billing and Shipping filds in Checkout page
*/
function wcis_checkout_fields($fields) {
  $order = array(
    'billing_first_name',
    'billing_last_name',
    'billing_email',
    'billing_phone',

    'billing_country',
    'billing_state',
    'billing_postcode',
    'billing_city',
    'billing_address_1',
    'billing_address_2',
  );

  $ordered_fields = array();
  foreach($order as $o) {
    $ordered_fields[$o] = $fields['billing'][$o];
  }

  $fields['billing'] = $ordered_fields;
  return $fields;
}
