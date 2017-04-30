<?php
/*
Plugin Name: WooCommerce Indonesia Shipping
Description: WooCommerce FREE Shipping plugin for JNE, TIKI, or POS. Requires purchase from RajaOngkir.
Plugin URI: http://github.com/hrsetyono/wc-indo-shipping
Author: The Syne Studio
Author URI: http://thesyne.com/
Version: 1.0.0-beta4
*/

if(!defined('ABSPATH') ) { exit; } // exit if accessed directly

// check if WooCommerce active
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if(!is_plugin_active('woocommerce/woocommerce.php') ) {
  return false;
}

define('WCIS_DIR', plugins_url('', __FILE__) );

require_once 'admin/all.php';
require_once 'public/all.php';

/*
  Inititate the Indo Shipping method
*/
new WCIS_Init();
class WCIS_Init {
  private $settings;
  private $enabled;

  function __construct() {
    $this->settings = get_option('woocommerce_wcis_settings');
    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'no';

    if($this->enabled === 'yes') {
      $this->init_classes();
    }

    // run this code even if disabled
    add_action('woocommerce_shipping_init', array($this, '_shipping_init') );
    add_filter('woocommerce_shipping_methods', array($this, '_shipping_method') );
  }

  /*
    Inititate the needed classes
  */
  function init_classes() {

    new WCIS_Ajax();
    new WCIS_Checkout();

    if(!is_admin() ) {
      new WCIS_Frontend();
    }

    // change default
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
  }


  /////

  /*
    Initiate WC Shipping

    @filter woocommerce_shipping_init
  */
  function _shipping_init() {
    require_once('admin/init-main.php');
    require_once('admin/init-zones.php');
  }

  /*
    Add our custom Shipping method

    @filter woocommerce_shipping_methods
  */
  function _shipping_method($methods) {
  	$methods['wcis'] = 'WCIS_Method';
    $methods['wcis_zone'] = 'WCIS_Zones_Method';
  	return $methods;
  }
}
