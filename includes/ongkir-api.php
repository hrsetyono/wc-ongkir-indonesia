<?php

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

    // this API is only for testing and not used anywhere
    register_rest_route(ONGKIR_NAMESPACE, '/cost/', [
      'methods' => 'POST',
      'callback' => [$this, 'test_cost_api'],
      'permission_callback' => '__return_true'
    ]);

    // @warn - disable this after updating the JSON files
    // register_rest_route(ONGKIR_NAMESPACE, '/update-json', [
    //   'methods' => 'GET',
    //   'permission_callback' => '__return_true',
    //   'callback' => [$this, 'update_json_files'],
    // ]);
  }

  /**
   * @get /cities/:prov_code
   * 
   * @return array - All cities within that province.
   */
  function get_cities_api($params) {
    if ($params['prov_code']) {
      $cities_raw = Ongkir_Data::get_cities($params['prov_code']);
    
      // map to `id:name`
      $cities = [];
      foreach ($cities_raw as $c) {
        $cities[$c['id']] = $c['name'];
      }
    
      $cities = [0 => __( 'Pilih Kota...', 'wc-ongkir-indonesia')] + $cities;
      return $cities;
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
    $cities_raw = Ongkir_Data::get_cities($params['prov_code']);
    $index = array_search($params['city_id'], array_column($cities_raw, 'id'));

    if ($index === false) {
      return new WP_Error('no_city', __('City not found', 'wc-ongkir-indonesia'));
    }
   
    $dists_raw = $cities_raw[$index]['districts'];
    $dists = [];
    foreach ($dists_raw as $d) {
      $dists[$d['id']] = $d['name'];
    }

    $dists = [ '0' => __('Pilih Kecamatan...', 'wc-ongkir-indonesia') ] + $dists;
    return $dists;
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
      $cities_raw = Ongkir_Data::get_cities($params['prov_code']);

      foreach ($cities_raw as $c) {
        foreach ($c['districts'] as $d) {
          // find district that has the same ID as the one passed on
          if ($params['district_id'] == $d['id']) {
            $city_id = $c['id'];
            $field_value = $c['name'] . ", {$d['name']} [{$d['id']}]";
            break;
          }
        }

        if ($city_id > 0) { break; }
      }
    }

    $cities_field = $this->_get_cities_field($params['type'], $params['prov_code'], $city_id);
    $districts_field = $this->_get_districts_field($params['type'], $params['prov_code'], $city_id, $field_value);

    return $cities_field . $districts_field;
  }

  /**
   * Get the list of cities and upload the JSON to wp-content/uploads. You need to move those files manually to this plugin's /includes/data for them to work
   */
  function update_json_files() {
    $api = new RajaOngkir();

    $upload = wp_upload_dir();
    $dir = trailingslashit($upload['basedir']) . 'wc-ongkir-indonesia';
    if (!file_exists($dir)) {
      wp_mkdir_p($dir);
    }

    // @warn - don't loop too much here because it will cause timeout
    // $provs = Ongkir_Data::get_provinces();
    $provs = [
      "KS" => 3,
      "KU" => 31,
      "SA" => 22,
      "ST" => 27,
      "SG" => 20,
      "PA" => 14,
      "PB" => 29
    ];

    foreach ($provs as $code => $id) {
      $cities = $api->get_city_list($id, true);

      if (!is_wp_error($cities)) {
        $file_path = $dir . "/{$code}.json";
        file_put_contents($file_path, json_encode($cities, JSON_PRETTY_PRINT));
        echo "Updated {$code}.json <br>";
      }
    }
  }

  /**
   *
   */
  function test_cost_api($request) {
    $params = $request->get_params();
    $api = new RajaOngkir();

    $args = [
      'origin' => $params['origin'],
      'destination' => $params['destination'],
      'weight' => $params['weight'],
      'courier' => $params['courier']
    ];

    $costs = $api->get_costs($args);
    return $costs;
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