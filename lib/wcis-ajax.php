<?php

class WCIS_Ajax {
	/*
	  Get list of Cities from Province Code
	*/
	static function get_cities() {
		$code = $_GET['code'];
		$id = WCIS_Data::get_province_id($code);

	  $settings = get_option('woocommerce_wcis_settings');

	  $api = new WCIS_API($settings['key']);
	  $response = $api->get_cities($id);
	  echo json_encode($response);

		wp_die();
	}

	/*
	  Get list of Districts from City ID
	*/
	static function get_districts() {
		$id = $_GET['id'];
	  $settings = get_option('woocommerce_wcis_settings');

	  $api = new WCIS_API($settings['key']);
	  $response = $api->get_districts($id);
	  echo json_encode($response);

		wp_die();
	}
}
