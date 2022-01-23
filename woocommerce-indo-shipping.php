<?php
/*
Plugin Name: WooCommerce Indo Shipping.
Description: WooCommerce FREE Shipping plugin for JNE, J&T, TIKI, POS, etc. Requires PRO License from RajaOngkir.
Plugin URI: http://github.com/hrsetyono/woocommerce-indo-shipping
Author: Pixel Studio
Author URI: https://pixelstudio.id/
Version: 2.1.1
*/

if(!defined('ABSPATH') ) { exit; } // exit if accessed directly

// Abort if WooCommerce not installed
if(!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  return;
}

define('WCIS_VERSION', '2.1.1');
define('WCIS_PATH', plugins_url('', __FILE__));
define('WCIS_DIR', __DIR__);
define('WCIS_NAMESPACE', 'wcis/v1');
define('WCIS_API', get_site_url() . '/wp-json/' . WCIS_NAMESPACE);


require_once __DIR__ . '/module-admin/wcis-checkout.php';
require_once __DIR__ . '/module-api/_index.php';
require_once __DIR__ . '/module-checkout/_index.php';

/**
 * Inititate the Indo Shipping method
 */
class WCIS_Init {
  private $settings;
  private $enabled;

  function __construct() {
    $this->settings = get_option('woocommerce_wcis_settings');
    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'no';

    if($this->enabled === 'yes') {
      $this->admin_init();

      add_action('template_redirect', [$this, 'public_init']);
    }

    // run this code even if disabled
    add_action('woocommerce_shipping_init', [$this, 'shipping_init']);
    add_filter('woocommerce_shipping_methods', [$this, 'shipping_method']);
  }

  /**
   * Inititate the needed classes
   */
  function admin_init() {
    new WCIS_Ajax();
    new WCIS_Checkout();
  }

  function public_init() {
    if(is_checkout() || is_cart()) {
      add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'], 1000000);
    }

    // change default
    // TODO: due to template_redirect action, Postcode might show up after refresh
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
  }


  /**
   * Initiate WC Shipping
   * @filter woocommerce_shipping_init
   */
  function shipping_init() {
    require_once('module-admin/init-main.php');
    require_once('module-admin/init-zones.php');
  }

  /**
   * Add our custom Shipping method
   * @filter woocommerce_shipping_methods
   */
  function shipping_method($methods) {
  	$methods['wcis'] = 'WCIS_Method';
    $methods['wcis_zone'] = 'WCIS_Zones_Method';
  	return $methods;
  }

  /**
   * @action wp_enqueue_scripts
   */
  function enqueue_assets() {
    $dist = WCIS_PATH . '/assets/dist';
    wp_enqueue_style('wcis_style', $dist . '/wcis-public.css', [], WCIS_VERSION);
    wp_enqueue_script( 'wcis_script', $dist . '/wcis-public.js', ['jquery'], WCIS_VERSION, true);
    
    wp_localize_script('wcis_script', 'wcisLocalize', [
      'WCIS_API' => WCIS_API,
    ]);
  }
}

new WCIS_Init();
