<?php

class WCIS_Ajax {

	function __construct() {
		add_action('wp_ajax_wcis_get_cities', array($this, 'get_cities') );
		add_action('wp_ajax_nopriv_wcis_get_cities', array($this, 'get_cities') );

		add_action('wp_ajax_wcis_get_districts', array($this, 'get_districts') );
		add_action('wp_ajax_nopriv_wcis_get_districts', array($this, 'get_districts') );
	}
	
	/*
	  Get list of Cities from Province Code
	*/
	function get_cities() {
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
	function get_districts() {
		$id = $_GET['id'];

	  $settings = get_option('woocommerce_wcis_settings');

	  $api = new WCIS_API($settings['key']);
	  $response = $api->get_districts($id, $settings['couriers']);
	  echo json_encode($response);

		wp_die();
	}
}
