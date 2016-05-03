<?php

/*
  AJAX translate province code into City list.
*/
function wcis_ajax_get_cities() {
	$code = $_GET['code'];
	$id = WCIS_Provinces::get_id($code);

  $settings = get_option('woocommerce_wcis_settings');

  $api = new WCIS_API($settings['key']);
  $response = $api->get_cities($id);
  echo json_encode($response);

	wp_die();
}

/*
  Get districts from city
*/
function wcis_ajax_get_districts() {
	$id = $_GET['id'];
  $settings = get_option('woocommerce_wcis_settings');

  $api = new WCIS_API($settings['key']);
  $response = $api->get_districts($id);
  echo json_encode($response);

	wp_die();
}
