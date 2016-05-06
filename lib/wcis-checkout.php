<?php
/*
  Reorder Billing and Shipping filds in Checkout page
*/
function wcis_checkout_fields($fields) {
  // Field Order
  $forms = array(
    'billing' => array(
      '_first_name', '_last_name',
      '_email', '_phone',

      '_country',
      '_state', '_postcode',
      '_city',
      '_address_1',
      '_address_2',
    ),

    'shipping' => array(
      '_first_name', '_last_name',
      '_country',
      '_state', '_postcode',
      '_city',
      '_address_1',
      '_address_2'
    )
  );

  foreach($forms as $f => $order) {
    $ordered_fields = array();
    foreach($order as $o) {
      $ordered_fields[$f . $o] = $fields[$f][$f . $o];
    }

    $fields[$f] = $ordered_fields;
  }

  return $fields;
}

/*
  Clean the User's city field from [id] notation used when calculating shipping cost. Only run when it's not Guest.

  @param int $user_id - The customer that bought this
  @param array $posted - The data posted
*/
function wcis_checkout_update_user_meta($user_id, $posted) {
  $city = _wcis_clean_city_field($posted['billing_city']);
  update_user_meta($user_id, 'billing_city', $city);

  // if shipping city is passed on
  if(isset($posted['shipping_city']) ) {
    $city = _wcis_clean_city_field($posted['shipping_city']);
  }
  update_user_meta($user_id, 'shipping_city', $city);
}

/*
  Clean the Order's city field from [id] notation used when calculating shipping cost.

  @param int $order_id - The order that just created
  @param array $posted - The data posted
*/
function wcis_checkout_update_order_meta($order_id, $posted) {
  $city = _wcis_clean_city_field($posted['billing_city']);
  update_post_meta($order_id, '_billing_city', $city);

  // if shipping city is passed on
  if(isset($posted['shipping_city']) ) {
    $city = _wcis_clean_city_field($posted['shipping_city']);
  }
  update_post_meta($order_id, '_shipping_city', $city);
}

/*
  Clean the city field. The raw formatt is "City name [id]". Remove the [id].

  @param string $city_raw
  @return string - City name without ID
*/
function _wcis_clean_city_field($city_raw) {
  preg_match('/[\w\s,]+/', $city_raw, $city);

  return trim($city[0]);
}
