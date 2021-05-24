<?php
/*
  Handle all AJAX call from front-end
*/
class WCIS_Ajax {

	function __construct() {
		add_action('wp_ajax_wcis_get_cities', array($this, 'get_cities') );
		add_action('wp_ajax_nopriv_wcis_get_cities', array($this, 'get_cities') );
	}

	/*
	  Get list of Cities from Province Code

		@filter wp_ajax_wcis_get_cities
	*/
	function get_cities() {
		$state = $_GET['state'];
		$id = wcis_get_province_id( $state );
		$cities = wcis_get_cities( $id );
	  echo json_encode($cities);

		wp_die();
	}
}
