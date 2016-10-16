<?php

/*
  Class to keep the global variable such as API key
*/
class WCIS_Method extends WC_Shipping_Method {
  private $api;

  public function __construct($instance_id = 0) {
		$this->id = 'wcis';
    $this->enabled = $this->get_option('enabled');

    $this->title = __('Indo Shipping', 'wcis');
		$this->method_title = __('Indo Shipping', 'wcis');
		$this->method_description = __('Indonesian domestic shipping with JNE, TIKI, or POS', 'wcis');

    $this->init_form_fields();

    // allow save setting
    add_action('woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

  /*
    Initiate global setting page for WCIS
  */
  function init_form_fields() {
    $enabled_field = array(
      'title' => __('Enable/Disable', 'wcis'),
      'type' => 'checkbox',
      'label' => __('Enable Indo Shipping', 'wcis'),
      'default' => 'yes'
    );

    $key_field = array(
      'title' => __('API Key', 'wcis'),
      'type' => 'text',
      'description' => __('Signup at <a href="http://rajaongkir.com/akun/daftar" target="_blank">rajaongkir.com</a> and choose Pro license (Paid). Paste the API Key here', 'wcis'),
    );

    $city_field = array(
      'title' => __('City Origin', 'wcis'),
      'type' => 'select',
      'class'    => 'wc-enhanced-select',
      'description' => __('Ship from where? <br> Change your province at General > Base Location', 'wcis'),
      'options' => array()
    );

    $this->form_fields = array(
      'key' => $key_field
    );

    // if key is valid, show the other setting fields
    if($this->check_key_valid() ) {
      $city_field['options'] = $this->_get_cities();

      $this->form_fields['enabled'] = $enabled_field;
      $this->form_fields['city'] = $city_field;

      // set service fields by each courier
      $couriers = WCIS_Data::get_couriers();
      foreach($couriers as $id => $name) {
        $this->form_fields[$id . '_services'] = array(
          'title' => $name,
          'type' => 'multiselect',
          'class' => 'wc-enhanced-select',
          'description' => __("Choose allowed services by { $name }.", 'wcis'),
          'options' => WCIS_Data::get_services($id, true)
        );
      }

    } // if valid
  }

  /*
    Check validation of Key by doing a sample AJAX call

    @return bool - Valid or not
  */
  private function check_key_valid() {
    $key = $this->settings['key'];

    if(!$key) { return false; } // ABORT if key is empty

    // initiate API
    $this->api = new WCIS_API($key);

    if(!$this->api->is_valid() ) {
      $error_msg = __('Invalid API Key. Is there empty space before / after it?', 'wcis');
      $this->form_fields['key']['description'] = '<span style="color:#f44336;">' . $error_msg . '</span>';
      return false;
    }
    else {
      $success_msg = __('API Connected!', 'wcis');
      $this->form_fields['key']['description'] = '<span style="color: #4caf50;">' . $success_msg . '</span>';
      return true;
    }

  }

  /*
    Get cities from API
    @return array - List of cities in base province
  */
  private function _get_cities() {
    $location = wc_get_base_location();
    $province_id = WCIS_Data::get_province_id($location['state']);

    $cities_raw = $this->api->get_cities($province_id);
    $cities = array();
    foreach($cities_raw as $c) {
      $cities[$c['city_id']] = $c['city_name'];
    }

    return $cities;
  }


}
