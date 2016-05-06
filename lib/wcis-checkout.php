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

    // Add Destination ID necessary for RajaOngkir API
    $fields[$f][$f . '_destination_id'] = array(
      'label' => __('Destination ID', 'wcis'),
      'placeholder' => __('Leave this empty', 'wcis'),
      'required' => false,
      'class' => array('form-row-wide'),
      'clear' => true
    );
  }

  return $fields;
}
