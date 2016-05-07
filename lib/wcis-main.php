<?php

class WCIS_Method extends WC_Shipping_Method {
  private $api;

  public function __construct() {
		$this->id = 'wcis';
    $this->title = __('Indo Shipping', 'wcis');
		$this->method_title = __('Indo Shipping', 'wcis');
		$this->method_description = __('Indonesian domestic shipping with JNE, TIKI, or POS', 'wcis');

		$this->init();

    // allow save setting
    add_action('woocommerce_update_options_shipping_methods', array(&$this, 'process_admin_options'));
	}

	/*
	  Init your settings
	*/
	function init() {
    $this->init_settings();

    // initiate API if key exists
    if(array_key_exists('enabled', $this->settings) ) {
      $this->enabled = $this->settings['enabled'];
    }

    if(array_key_exists('key', $this->settings) ) {
      $this->api = new WCIS_API($this->settings['key']);
    }

		$this->init_form_fields();

		// Save settings in admin if you have any defined
		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
	}

  /*
    Create Settings form
  */
  function init_form_fields() {
    $key_field = array(
      'title' => __('API Key', 'wcis'),
      'type' => 'text',
      'description' => __('Signup at <a href="http://rajaongkir.com/akun/daftar" target="_blank">rajaongkir.com</a> and choose Starter license (Free). Paste the API Key here', 'wcis'),
    );

    $fields = array();

    $key_exists = isset($this->settings['key']);
    $key_valid = $this->api->is_valid();

    // only show if Key has been filled AND valid
    if($key_exists && $key_valid) {

      $enabled_field = array(
        'title' => __('Enable/Disable', 'wcis'),
        'type' => 'checkbox',
        'label' => __('Enable Indo Shipping', 'wcis'),
        'default' => 'yes'
      );

      $city_field = array(
        'title' => __('City Origin', 'wcis'),
        'type' => 'select',
        'class'    => 'wc-enhanced-select',
        'description' => __('Your shop\'s base city. Change your province at General > Base Location', 'wcis'),
        'options' => $this->_get_cities()
      );

      $couriers_field = array(
        'title' => __('Couriers', 'wcis'),
        'type' => 'multiselect',
        'class' => 'wc-enhanced-select',
        'description' => __('Choose the couriers you want to use. You can select multiple.', 'wcis'),
        'options' => WCIS_Data::get_couriers()
      );

      $key_success = __('API Connected!', 'wcis');
      $key_field['description'] = '<span style="color: #4caf50;">' . $key_success . '</span>';

      $fields = array(
        'enabled' => $enabled_field,
        'key' => $key_field,
        'city' => $city_field,
        'couriers' => $couriers_field
      );

      // if couriers already chosen
      if(isset($this->settings['couriers']) ) {
        foreach($this->settings['couriers'] as $c) {
          $title = strtoupper($c);

          // get services
          $services_raw = WCIS_Data::get_services($c);
          $services = array();
          foreach($services_raw as $key => $s) {
            $services[$key] = isset($s['title']) ? $s['title'] : $s;
          }

          $service_field = array(
            'title' => $title . ' Services',
            'type' => 'multiselect',
            'class' => 'wc-enhanced-select',
            'description' => __("Choose the allowed services by {$title}", 'wcis'),
            'options' => $services
          );

          $fields[$c . '_services'] = $service_field;
        }
      }
    }
    // if key has been fileld BUT invalid
    elseif($key_exists && !$key_valid) {
      $key_error = __('Invalid API Key. Is there empty space behind it?', 'wcis');
      $key_field['description'] = '<span style="color:#f44336;">' . $key_error . '</span>';
      $fields = array('key' => $key_field);
    }
    // if key is empty
    else {
      $fields = array('key' => $key_field);
    }

    $this->form_fields = $fields;
  }

	/*
	  Calculate_shipping function.
	  @param mixed $package
	*/
	function calculate_shipping($package) {
    // if district not exists or empty
    $id_exists = array_key_exists('destination_id', $package['destination']);
    if(!$id_exists || empty($package['destination']['destination_id']) ) {
      return false;
    }

    $couriers_cost = $this->_get_costs($package);
    $this->_set_rate($couriers_cost);
	}

  /////

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

  /*
    Get the costs from selected Couriers.

    @param array $package - The shipping detail
    @return array - List of cost grouped for each courier
  */
  private function _get_costs($package) {
    $weight = $this->_calculate_weight($package);

    // form the args accepted by rajaongkir
    $args = array();
    foreach($this->settings['couriers'] as $courier) {
      $args[] = array(
        'origin' => $this->settings['city'],
        'originType' => 'city',
        'destination' => $package['destination']['destination_id'],
        'destinationType' => 'subdistrict',
        'weight' => $weight,
        'courier' => $courier
      );
    }

    // get list of couriers and its cost
    $couriers = array();
    foreach($args as $a) {
      $couriers[] = $this->api->get_costs($a);
    }

    return $couriers;
  }

  /*
    Set the Rate based on Cost list from API

    @param array $couriers_cost - Cost list from API
  */
  private function _set_rate($couriers_cost) {
    // format the costs from API to WooCommerce
    foreach($couriers_cost as $courier):

      // get full list of services
      $code = $courier[0]['code'];
      $all_services = WCIS_Data::get_services($code);

      // get allowed service from this courier
      $setting_id = $code . '_services';
      $allowed_services = isset($this->settings[$setting_id]) ? $this->settings[$setting_id] : array();

      foreach($courier[0]['costs'] as $service):

        // check if this service is allowed
        $is_allowed = false;
        foreach($allowed_services as $as) {
          // if has variation
          if(isset($all_services[$as]['vars']) ) {
            $is_allowed = in_array($service['service'], $all_services[$as]['vars'] );
          }
          else {
            $is_allowed = $service['service'] === $as;
          }

          if($is_allowed) { break; }
        }

        if($is_allowed) {
          $rate = array(
            'id' => $code . '_' . strtolower($service['service']),
            'label' => strtoupper($code) . ' ' . $service['service'],
            'cost' => $service['cost'][0]['value'],
            'calc_tax' => 'per_order'
          );

          $this->add_rate($rate);
        }
      endforeach;
    endforeach;
  }

  /*
    Calculate the weight of items in cart. If all items has no weight specified, it will return 1.

    @return int - THe weight in the unit specified in admin.
  */
  private function _calculate_weight($package) {
    global $woocommerce;

    $weight = $woocommerce->cart->cart_contents_weight;

    if($weight > 0) { return $weight; }
    else { return 1; }
  }

}
