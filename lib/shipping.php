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

    // only show if Key has been filled AND valid
    $key_exists = array_key_exists('key', $this->settings);
    $key_valid = $this->api->is_valid();
    if($key_exists && $key_valid) {

      $cities = $this->_get_cities();
      $couriers = $this->_get_couriers();

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
        'options' => $cities
      );

      $couriers_field = array(
        'title' => __('Couriers', 'wcis'),
        'type' => 'multiselect',
        'class' => 'wc-enhanced-select',
        'description' => __('Choose the couriers you want to use. You can select multiple.', 'wcis'),
        'options' => $couriers
      );

      $key_success = __('Key accepted!', 'wcis');
      $key_field['description'] = '<span style="color: #4caf50;">' . $key_success . '</span>';

      $fields = array(
        'enabled' => $enabled_field,
        'key' => $key_field,
        'city' => $city_field,
        'couriers' => $couriers_field
      );

      // if couriers already chosen
      if(array_key_exists('couriers', $this->settings) ) {
        foreach($this->settings['couriers'] as $c) {
          $title = strtoupper($c);

          // get services
          $services_raw = $this->_get_services($c);
          $services = array();
          foreach($services_raw as $key => $s) {
            $services[$key] = $s['title'];
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
    // if api key is invalid
    elseif($key_exists && !$key_valid) {
      $key_error = __('Invalid API Key. Is there empty space behind it?', 'wcis');
      $key_field['description'] = '<span style="color:#f44336;">' . $key_error . '</span>';
      $fields = array('key' => $key_field);
    }
    // if api key empty
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
    $weight = $this->_calculate_weight($package);
    $destination_id = $this->_get_city_id($package['destination']['city']);

    // if city or destination_id empty
    if(!$package['destination']['city'] || !$destination_id) {
      return false;
    }

    // form the args accepted by rajaongkir
    $args = array();
    foreach($this->settings['couriers'] as $courier) {
      $args[] = array(
        'origin' => $this->settings['city'],
        'destination' => $destination_id,
        'weight' => $weight,
        'courier' => $courier
      );
    }

    // get list of costs
    $costs = array();
    foreach($args as $a) {
      $costs[] = $this->api->get_costs($a);
    }

    // format the costs from API to WooCommerce
    foreach($costs as $courier) {
      foreach($courier[0]['costs'] as $service) {
        $code = $courier[0]['code'];

        // TODO: this assuming the client filled the service
        // if included in allowed service
        $setting_id = $code . '_services';

        $all_services = $this->_get_services($code);
        $allowed_services = (array_key_exists($setting_id, $this->settings) ) ? $this->settings[$setting_id] : array();

        $is_allowed = false;
        foreach($allowed_services as $s) {
          $is_allowed = in_array($service['service'], $all_services[$s]['vars'] );
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
      }
    }
	}

  /////

  private function _calculate_weight($package) {
    global $woocommerce;

    $weight = $woocommerce->cart->cart_contents_weight;

    if($weight > 0) { return $weight; }
    else { return 1; }
  }

  /*
    Get cities from API

    @param boolean $raw - Get raw data or clean one
    @return array - List of cities in base province
  */
  private function _get_cities($raw = false) {
    $location = wc_get_base_location();
    $province_id = WCIS_Provinces::get_id($location['state']);

    $cities_raw = $this->api->get_cities($province_id);
    if($raw) { return $cities_raw; }

    $cities = array();
    foreach($cities_raw as $c) {
      $cities[$c['city_id']] = $c['city_name'];
    }

    return $cities;
  }

  /*
    Convert City name to Id

    @param string $city_name
    @return int - The ID of the city, 0 if not found.
  */
  private function _get_city_id($city_name) {
    $cities = $this->_get_cities(true);

    foreach($cities as $c) {
      if($c['city_name'] === $city_name) {
        return $c['city_id'];
      }
    }

    return 0;
  }

  // get form fields
  private function _get_form_fields() {

  }

  // get couriers list
  private function _get_couriers() {
    return array(
      'jne' => 'JNE',
      'tiki' => 'TIKI',
      'pos' => 'POS Indonesia'
    );
  }

  /*
    Get courier services list

    @param string $courier - The lowercased name of the courier
    @return array - The list of services provided by this courier and its variation
  */
  private function _get_services($courier) {
    switch($courier) {
      case 'jne':
        return array(
          'OKE' => array(
            'title' => 'OKE - Ongkos Kirim Ekonomis',
            'vars' => array('OKE', 'CTCOKE')
          ),
          'REG' => array(
            'title' => 'REG - Layanan Reguler',
            'vars' => array('REG', 'CTC')
          ),
          'YES' => array(
            'title' => 'YES - Yakin Esok Sampai',
            'vars' => array('YES', 'CTCYES')
          ),
          'JTR' => array(
            'title' => 'JTR - JNE Trucking',
            'vars' => array('JTR', 'JTR<150', 'JTR250', 'JTR>250')
          ),
          'SPS' => array(
            'title' => 'SPS - Super Speed',
            'vars' => array('SPS')
          ),
        );
        break;

      case 'tiki':
        return array(
          'ECO' => array('title' => 'ECO - Economi Service', 'vars' => array('ECO') ),
          'REG' => array('title' => 'REG - Reguler Service', 'vars' => array('REG') ),
          'ONS' => array('title' => 'ONS - Over Night Service', 'vars' => array('ONS') ),
          'HDS' => array('title' => 'HDS - Holiday Delivery Service', 'vars' => array('HDS') ),
          'SDS' => array('title' => 'SDS - Same Day Service', 'vars' => array('SDS') )
        );
        break;

      case 'pos':
        return array(
          'Surat Kilat Khusus' => array('title' => 'Surat Kilat Khusus', 'vars' => array('Surat Kilat Khusus') ),
          'Express Next Day' => array('title' => 'Express Next Day', 'vars' => array('Express Next Day') )
        );
        break;
    }
  }
}
