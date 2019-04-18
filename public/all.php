<?php
require_once 'wcis-ajax.php';
require_once 'wcis-frontend.php';

/**
 * Re-order field checkout
 * Move field City after state
 */
add_filter( 'woocommerce_default_address_fields', 'shipping_indo_reorder_checkout_fields' );
  
function shipping_indo_reorder_checkout_fields( $fields ) {
  $fields['state']['priority'] = 70;
  $fields['city']['priority'] = 80;
  return $fields;
}

/**
 * Remove existing address on checkout to fix city show empty field
 */
add_filter( 'woocommerce_checkout_get_value', 'indo_shipping_clear_state_data', 10, 2 );

function indo_shipping_clear_state_data( $value, $input ) {
    $shipping_fields = array(
        'shipping_city', 'shipping_state'
    );

    if ( in_array( $input, $shipping_fields ) ) {
        $value = '';
    }

    return $value;
}