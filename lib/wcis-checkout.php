<?php
/*
  Reorder Billing and Shipping filds in Checkout page
*/
function wcis_checkout_fields($fields) {
  // BILLING
  $order = array(
    'billing_first_name',
    'billing_last_name',
    'billing_email',
    'billing_phone',

    'billing_country',
    'billing_state',
    'billing_postcode',
    'billing_city',
    'billing_address_1',
    'billing_address_2',
  );

  $ordered_fields = array();
  foreach($order as $o) {
    $ordered_fields[$o] = $fields['billing'][$o];
  }

  $fields['billing'] = $ordered_fields;

  /*
  $fields['billing']['billing_district'] = array(
    'label' => __('District / Kecamatan', 'wcis'),
    'placeholder' => __('District / Kecamatan', 'wcis'),
    'required' => false,
    'class' => array('form-row-wide'),
    'clear' => true
  );
  */

  // ORDER

  return $fields;
}
