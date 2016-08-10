<?php

class WCIS_Method extends WC_Shipping_Method {
  private $api;

  public function __construct($instance_id = 0) {
		$this->id = 'wcis';
    $this->instance_id = absint($instance_id);
    $this->enabled = $this->get_option('enabled');

    $this->title = __('Indo Shipping', 'wcis');
		$this->method_title = __('Indo Shipping', 'wcis');
		$this->method_description = __('Indonesian domestic shipping with JNE, TIKI, or POS', 'wcis');

    $this->supports = array('shipping-zones', 'instance-settings');

    $this->init_form_fields();

    // allow save setting
    add_action('woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

  /*
    Create Settings form
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

    $fields = array(
      'key' => $key_field
    );

    $this->instance_form_fields = $fields;
    $key = $this->get_option('key');

    // ABORT if key is empty
    if(!$key) { return false; }

    // initiate API
    $this->api = new WCIS_API($key);

    // ABORT if key is not valid
    if(!$this->api->is_valid() ) {
      $error_msg = __('Invalid API Key. Is there empty space behind it?', 'wcis');
      $this->instance_form_fields['key']['description'] = '<span style="color:#f44336;">' . $error_msg . '</span>';
      return false;
    }

    $success_msg = __('API Connected!', 'wcis');
    $this->instance_form_fields['key']['description'] = '<span style="color: #4caf50;">' . $success_msg . '</span>';

    // Add extra fields after API validation

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

    $this->instance_form_fields['enabled'] = $enabled_field;
    $this->instance_form_fields['city'] = $city_field;
    $this->instance_form_fields['couriers'] = $couriers_field;

    // if couriers already chosen
    if($this->get_option('couriers') ) {
      foreach($this->get_option('couriers') as $c) {
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

        $this->instance_form_fields[$c . '_services'] = $service_field;
      }
    }
  }

	/*
	  Calculate_shipping function.
	  @param mixed $package
	*/
	function calculate_shipping($package = array() ) {
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

      if(empty($courier) ) { break; }

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
            'id' => $code . '_' . strtolower($service['service']) . $this->instance_id,
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

    if($weight > 0) {
      return $weight;
    }
    // if no weight data, return default weight or 1kg
    else {
      $weight = (int) ceil(apply_filters('wcis_default_weight', $package) );

      return (is_int($weight) && $weight > 0 ) ? $weight : 1;
    }
  }

}
