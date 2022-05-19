<?php

if (!class_exists('Ongkir_Admin')):

class Ongkir_Admin {
  function __construct($enabled = 'no') {
    // run this code even if disabled
    add_action('woocommerce_shipping_init', [$this, 'shipping_init']);
    add_filter('woocommerce_shipping_methods', [$this, 'shipping_method']);
  }

  /**
   * Initiate WC Shipping
   * 
   * @filter woocommerce_shipping_init
   */
  function shipping_init() {
    require_once __DIR__ . '/ongkir-method.php';
    require_once __DIR__ . '/ongkir-zone.php';
  }

  /**
   * Add our custom Shipping method
   * 
   * @filter woocommerce_shipping_methods
   */
  function shipping_method($methods) {
  	$methods['wcis'] = 'Ongkir_Method';
    $methods['wcis_zone'] = 'Ongkir_Zone';
  	return $methods;
  }
}

endif;