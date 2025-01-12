<?php

require_once __DIR__ . '/ongkir-data.php';

if (!class_exists('Ongkir_API')):

class Ongkir_API {
  function __construct() {
    add_action('rest_api_init', [$this, 'register_api']);
  }

  /**
   * Initiate the API required to get cities name
   * 
   * @action rest_api_init
   */
  function register_api() {
    register_rest_route(ONGKIR_NAMESPACE, '/cities/(?P<prov_code>\w+)/', [
      'methods' => 'GET',
      'callback' => [$this, 'get_cities_api'],
      'permission_callback' => '__return_true'
    ]);

    register_rest_route(ONGKIR_NAMESPACE, '/districts/(?P<prov_code>\w+)/(?P<city_id>\d+)/', [
      'methods' => 'GET',
      'callback' => [$this, 'get_districts_api'],
      'permission_callback' => '__return_true'
    ]);

    register_rest_route(ONGKIR_NAMESPACE, '/fields/(?P<type>\w+)/(?P<prov_code>\w+)/(?P<district_id>\d+)', [
      'methods' => 'GET',
      'callback' => [$this, 'get_fields_api'],
      'permission_callback' => '__return_true'
    ]);
  }

  /**
   * @get /cities/:prov_code
   * 
   * @return array - All cities within that province.
   */
  function get_cities_api($params) {
    if ($params['prov_code']) {
      $prov_id = Ongkir_Data::get_province_id($params['prov_code']);
      $cities = Ongkir_Data::get_cities($prov_id);
    
      // map to `id:name`
      $cities_mapped = [];
      foreach ($cities as $id => $item) {
        $cities_mapped[$id] = $item['city_name'];
      }
    
      $cities_mapped = [0 => __( 'Pilih Kota...', 'wc-ongkir-indonesia')] + $cities_mapped;
      return $cities_mapped;
    }
    // If province code not given, show error message
    else {
      return [
        0 => __('Pilih Provinsi terlebih dahulu...', 'wc-ongkir-indonesia')
      ];
    }
  }

  /**
   * @get /districts/:prov_code/:city_id
   * 
   * @return array - All districts within that city.
   */
  function get_districts_api($params) {
    $prov_id = Ongkir_Data::get_province_id($params['prov_code']);
    $cities = Ongkir_Data::get_cities($prov_id);
    $city = $cities[$params['city_id']] ?? null;
    
    // abort if city not found
    if (!$city) { return; }
    
    // Format the district value so the City name is included
    $districts = [ '0' => __('Pilih Kecamatan...', 'wc-ongkir-indonesia') ];
    foreach ($city['districts'] as $id => $name) {
      $districts[$city['city_name'] . ", $name [$id]"] = $name;
    }

    return $districts;
  }


  /**
   * Get both of the form fields
   * 
   * @get /fields/:type/:prov_code/:district_id
   * @return array
   */
  function get_fields_api($params) {
    $city_id = 0;
    $field_value = '';
  
    // Convert District ID to City ID
    if ($params['district_id'] != '0') {
      $prov_id = Ongkir_Data::get_province_id($params['prov_code']);
      $cities = Ongkir_Data::get_cities($prov_id);

      foreach ($cities as $id => $c) {
        foreach ($c['districts'] as $dist_id => $dist_name) {
          // find district that has the same ID as the one passed on
          if ($params['district_id'] == $dist_id) {
            $city_id = $id;
            $field_value = $c['city_name'] . ", {$dist_name} [{$dist_id}]";
            break;
          }
        }

        if ($city_id > 0) {
          break;
        }
      }
    }

    $cities_field = $this->_get_cities_field($params['type'], $params['prov_code'], $city_id);
    $districts_field = $this->_get_districts_field($params['type'], $params['prov_code'], $city_id, $field_value);

    return $cities_field . $districts_field;
  }



  /**
   * Get the form field for City Dropdown
   * 
   * @return array - Currently only has one item: 'field' which contains the HTML form field.
   */
  private function _get_cities_field($type, $prov_code = '0', $city_id = 0) {
    $field = '';

    // if code is 0, show empty placeholder dropdown
    if ($prov_code == '0') {
      $field = woocommerce_form_field("_{$type}_city", [
        'type' => 'select',
        'label' => __('City', 'wc-ongkir-indonesia'),
        'options' => [0 => __('Pilih Provinsi terlebih dahulu...', 'wc-ongkir-indonesia')],
        'return' => true,
        'required' => true,
      ]);
    }
    // else
    else {
      $cities = $this->get_cities_api(['prov_code' => $prov_code]);

      $field = woocommerce_form_field("_{$type}_city", [
        'type' => 'select',
        'label' => __('City', 'wc-ongkir-indonesia'),
        'options' => $cities,
        'return' => true,
        'required' => true,
      ], $city_id);
    }

    return $field;
  }

  /**
   * Get the form field for District Dropdown
   * 
   * @return array - City ID and the HTML form field. City ID is used to pre-select the dropdown
   */
  private function _get_districts_field(
    $type,
    $prov_code,
    $city_id = 0,
    $field_value = ''
  ) {
    $field = '';

    // If city ID is empty, show placeholder
    if ($city_id === 0) {
      $field = woocommerce_form_field("_{$type}_district", [
        'type' => 'select',
        'label' => __('Kecamatan', 'wc-ongkir-indonesia'),
        'options' => [ 0 => __('Pilih Kota terlebih dahulu...', 'wc-ongkir-indonesia') ],
        'return' => true,
        'required' => true
      ] );
    }
    // Else, show the district selection
    else {
      $districts = $this->get_districts_api([
        'prov_code' => $prov_code,
        'city_id' => $city_id
      ]);

      $field = woocommerce_form_field("_{$type}_district", [
        'type' => 'select',
        'label' => __('Kecamatan', 'wc-ongkir-indonesia'),
        'options' => $districts,
        'return' => true,
        'required' => true
      ], $field_value);
    }

    return $field;
  }
}

endif;