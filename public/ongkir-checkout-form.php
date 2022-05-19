<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('Ongkir_Checkout')):

class Ongkir_Checkout {
  function __construct() {
    add_filter('woocommerce_default_address_fields', [$this, 'reorder_checkout_fields']);
    add_filter('woocommerce_checkout_get_value', [$this, 'clear_state_data'], 10, 2);
  }

  /**
   * Re-order field checkout and Move field City after state
   * 
   * @filter woocommerce_default_address_fields
   */
  function reorder_checkout_fields($fields) {
    $fields['state']['priority'] = 70;
    $fields['city']['priority'] = 80;
    return $fields;
  }

  /**
   * Remove existing address on checkout to fix city show empty field
   * 
   * @filter woocommerce_checkout_get_value
   */
  function clear_state_data($value, $input) {
    $shipping_fields = [
      'shipping_city', 'shipping_state'
    ];

    if (in_array($input, $shipping_fields)) {
      $value = '';
    }

    return $value;
  }
}

new Ongkir_Checkout();
endif;