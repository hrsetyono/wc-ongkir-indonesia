<?php

add_filter( 'woocommerce_default_address_fields', 'wcis_reorder_checkout_fields' );
add_filter( 'woocommerce_checkout_get_value', 'wcis_clear_state_data', 10, 2 );

/**
 * Re-order field checkout
 * Move field City after state
 * 
 * @filter woocommerce_default_address_fields
 */
function wcis_reorder_checkout_fields( $fields ) {
  $fields['state']['priority'] = 70;
  $fields['city']['priority'] = 80;
  return $fields;
}

/**
 * Remove existing address on checkout to fix city show empty field
 * 
 * @filter woocommerce_checkout_get_value
 */
function wcis_clear_state_data( $value, $input ) {
  $shipping_fields = array(
    'shipping_city', 'shipping_state'
  );

  if( in_array( $input, $shipping_fields ) ) {
    $value = '';
  }

  return $value;
}