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

