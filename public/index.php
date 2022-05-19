<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('Ongkir_Public')):

class Ongkir_Public {
  function __construct($enabled = 'no') {
    // abort if not enabled
    if ($enabled !== 'yes') { return; }

    require_once __DIR__ . '/ongkir-hooks.php';
    require_once __DIR__ . '/ongkir-checkout-form.php';

    add_action('template_redirect', [$this, 'template_redirect']);
  }

  /**
   * @action template_redirect
   */
  function template_redirect() {
    if (is_checkout() || is_cart()) {
      add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'], 1000000);
    }

    // Change the default shipping calculator fields
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
  }

  /**
   * @action wp_enqueue_scripts
   */
  function enqueue_assets() {
    wp_enqueue_style('ongkir_style', ONGKIR_FILE . '/dist/ongkir-public.css', [], ONGKIR_VERSION);
    wp_enqueue_script('ongkir_script', ONGKIR_FILE . '/dist/ongkir-public.js', ONGKIR_VERSION, true);
    
    wp_localize_script('ongkir_script', 'ongkirLocalize', [
      'ONGKIR_API' => ONGKIR_API,
    ]);
  }
}

endif;