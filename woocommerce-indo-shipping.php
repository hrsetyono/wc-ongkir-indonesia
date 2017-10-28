<?php
/*
Plugin Name: WooCommerce Indonesia Shipping
Description: WooCommerce FREE Shipping plugin for JNE, TIKI, or POS. Requires purchase from RajaOngkir.
Plugin URI: http://github.com/hrsetyono/wc-indo-shipping
Author: The Syne Studio
Author URI: http://thesyne.com/
Version: 1.1.3
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
      $this->admin_init();

      add_action('template_redirect', array($this, 'public_init') );
    }

    // run this code even if disabled
    add_action('woocommerce_shipping_init', array($this, 'shipping_init') );
    add_filter('woocommerce_shipping_methods', array($this, 'shipping_method') );
  }

  /*
    Inititate the needed classes
  */
  function admin_init() {
    new WCIS_Ajax();
    new WCIS_Checkout();
  }

  function public_init() {
    if(is_checkout() ) {
      new WCIS_Frontend();
    }

    // change default
    // TODO: due to template_redirect action, Postcode might show up after refresh
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
  }


  /////

  /*
    Initiate WC Shipping

    @filter woocommerce_shipping_init
  */
  function shipping_init() {
    require_once('admin/init-main.php');
    require_once('admin/init-zones.php');
  }

  /*
    Add our custom Shipping method

    @filter woocommerce_shipping_methods
  */
  function shipping_method($methods) {
  	$methods['wcis'] = 'WCIS_Method';
    $methods['wcis_zone'] = 'WCIS_Zones_Method';
  	return $methods;
  }
}
