<?php

/**
 * Get both of the form fields
 * 
 * @get /fields/:type/:prov_code/:district_id
 * @return array
 */
function wcis_get_fields_api($params) {
  // Convert District ID to City ID
  if($params['district_id'] != '0') {
    $prov_id = wcis_get_province_id($params['prov_code']);
    $cities = _wcis_read_json("$prov_id.json");

    foreach($cities as $city_id => $c) {
      foreach($c['districts'] as $district_id => $name) {
        // find district that has the same ID as the one passed on
        if($params['district_id'] == $district_id) {
          $params['city_id'] = $city_id;
          break;
        }
      }
    }
  } else {
    $params['city_id'] = '0';
  }

  $cities_field = wcis_get_cities_field($params['type'], $params['prov_code']);
  $districts_field = wcis_get_districts_field($params['type'], $params['prov_code'], $params['city_id']);

  // add selected attribute
  if(isset($params['city_id'])) {
    $cities_field = preg_replace(
      '/(_city_field[\s\S]+)(\"' . $params['city_id'] . '\"\s)(\>)([\s\S]+\/p>)/Ui',
      '$1$2selected$3$4',
      $cities_field
    );
  }

  if($params['district_id'] != '0') {
    $districts_field = preg_replace(
      '/(_district_field[\s\S]+)(' . $params['district_id'] . '\]\"\s)(\>)([\s\S]+\/p>)/Ui',
      '$1$2selected$3$4',
      $districts_field
    );
  }

  return $cities_field . $districts_field;
}


/**
 * @get /cities/:prov_code
 * 
 * @return array - All cities within that province.
 */
function wcis_get_cities_api($params) {
  if($params['prov_code']) {
    $prov_id = wcis_get_province_id($params['prov_code']);
    $cities = wcis_get_cities($prov_id);
  
    // map to `id:name`
    $cities_mapped = [];
    foreach($cities as $id => $item) {
      $cities_mapped[$id] = $item['city_name'];
    }
  
    $cities_mapped = [0 => __( 'Pilih Kota...' )] + $cities_mapped;
    return $cities_mapped;
  }
  // If province code not given, show error message
  else {
    return [
      0 => __('Pilih Provinsi terlebih dahulu...')
    ];
  }
}


/**
 * @get /districts/:prov_code/:city_id
 * 
 * @return array - All districts within that city.
 */
function wcis_get_districts_api($params) {
  $prov_id = wcis_get_province_id($params['prov_code']);
  $cities = _wcis_read_json($prov_id . '.json');
  $city = $cities[$params['city_id']] ?? null;
  
  // abort if city not found
  if(!$city) { return; }
  
  // Format the district value so the City name is included
  $districts = [ '0' => __('Pilih Kecamatan...') ];
  foreach($city['districts'] as $id => $name) {
    $districts[$city['city_name'] . ", $name [$id]"] = $name;
  }

  return $districts;
}


/**
 * Get the form field for City Dropdown
 * 
 * @return array - Currently only has one item: 'field' which contains the HTML form field.
 */
function wcis_get_cities_field($type, $prov_code = '0') {
  $field = '';

  // if code is 0, show empty placeholder dropdown
  if($prov_code == '0') {
    $field = woocommerce_form_field( "_{$type}_city", [
      'type' => 'select',
      'label' => __('City', 'woocommerce'),
      'options' => [0 => __('Pilih Provinsi terlebih dahulu...')],
      'return' => true,
      'required' => true,
    ] );
  }
  // else
  else {
    $cities = wcis_get_cities_api(['prov_code' => $prov_code]);

    $field = woocommerce_form_field("_{$type}_city", [
      'type' => 'select',
      'label' => __('City', 'woocommerce'),
      'options' => $cities,
      'return' => true,
      'required' => true,
    ] );
  }

  return $field;
}

/**
 * Get the form field for District Dropdown
 * 
 * @return array - City ID and the HTML form field. City ID is used to pre-select the dropdown
 */
function wcis_get_districts_field($type, $prov_code, $city_id = '0') {
  $field = '';

  // If city ID is empty, show placeholder
  if($city_id == '0') {
    $field = woocommerce_form_field("_{$type}_district", [
      'type' => 'select',
      'label' => __('Kecamatan'),
      'options' => [ 0 => __('Pilih Kota terlebih dahulu...') ],
      'return' => true,
      'required' => true
    ] );
  }
  // Else, show the district selection
  else {
    $districts = wcis_get_districts_api([
      'prov_code' => $prov_code,
      'city_id' => $city_id
    ]);

    $field = woocommerce_form_field("_{$type}_district", [
      'type' => 'select',
      'label' => __('Kecamatan'),
      'options' => $districts,
      'return' => true,
      'required' => true
    ]);
  }

  return $field;
}



/**
 * Get all cities in a province
 */
function wcis_get_cities($prov_id) {
  $data = _wcis_read_json($prov_id . '.json');

  // if exists, scan for duplicate city name
  if($data) {
    $data = _wcis_prefix_dupe_city_name($prov_id, $data);
    return $data;
  } else {
    return $data;
  }
}

/**
 * Translate Province Code into ID based on Raja Ongkir list.
 * 
 * @param str $state - State / Province code from WooCommerce
 * @return int - the province's ID
 */
function wcis_get_province_id($prov_code) {
  $provinces = _wcis_read_json('provinces.json');
  $id = array_key_exists($prov_code, $provinces) ? $provinces[$prov_code] : 0;
  return $id;
}

/**
 * Get all provinces in Code:ID format. Code is the abbreviation from WooCommerce, ID is the number from RajaOngkir
 */
function wcis_get_provinces() {
  $provinces = _wcis_read_json('provinces.json');
  return $provinces;
}

/**
 * Get all couriers.
 * 
 * Available: pos,tiki,jne,pcp,esl,rpx,pandu,wahana,jnt,pahala,cahaya, sap,jet,indah,dse,slis,first,ncs,star
 * 
 * @return array - List of couriers in (slug => name) format.
 */
function wcis_get_couriers() {
  $couriers_raw = _wcis_read_json('couriers.json');

  // remap
  $couriers = [];
  foreach($couriers_raw as $key => $value) {
    $couriers[$key] = $value['name'];
  }

  return $couriers;
}

/**
 * Get the services provided by the courier.
 * 
 * // TODO: code stinks. Remove hardcode for J&T and simple_format parameter
 * @param string $name - Courier slug as listed in couriers.json
 * @param bool $simple_format - *Optional*. If true, return a simplified `id => name` format. Default is false.
 * @return array - The services this courier provided
 */
function wcis_get_services($name, $simple_format = false) {

  $couriers = _wcis_read_json('couriers.json');
  $courier = isset($couriers[$name]) ? $couriers[$name] : null;

  // if courier found
  if($courier) {
    $services = $courier['services'];

    // simplify the data
    if($simple_format) {
      $parsed = [];
      foreach($courier['services'] as $key => $val) {
        $parsed[$key] = $val['title'];
      }

      $services = $parsed;
    }

    return $services;
  }
}


//////

/**
 * Get JSON file inside this directory
 * 
 * @param string $filename
 * @return array
 */
function _wcis_read_json($filename) {
  $fileurl = WCIS_PATH . "/module-api/json/$filename";
  $args = [
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
    ],
  ];

  $fileraw = file_get_contents($fileurl, false, stream_context_create($args));
  return json_decode($fileraw, true);
}

/**
 * Add prefix to city and district that have the same name
 * 
 * @param array $data - Cities data
 * @param array $dupe_ids - ID of cities that share the same name
 * @return array - Prefixed list
 */
function _wcis_prefix_dupe_city_name($prov_id, $data) {
  // Province that has same city and district name
  $city_dupe_name = [
    3 => [402, 403, 455, 456], // Serang, Tangerang
    9 => [22, 23, 54, 55, 78, 79, 108, 109, 430, 431, 468, 469], // Bandung, Bekasi, Bogor, Cirebon, Sukabumi, Tasikmalaya
    10 => [249, 250, 348, 349, 472, 473, 398, 399], // Magelang, Pekalongan, Tegal, Semarang
    11 => [74, 75, 178, 179, 255, 256, 247, 248, 289, 290, 342, 343, 369, 370], // Blitar, Kediri, Malang, Madiun, Mojokerto, Pasuruan, Probolinggo,

    32 => [420, 421], // Solok

    22 => [68, 69], // Bima
    23 => [212, 213], // Kupang

    12 => [364, 365], // Pontianak
    7 => [129, 130], // Gorontalo

    24 => [157, 158], // Jayapura
    25 => [424, 425], // Sorong
  ];

  // if province has duplicate city name, add
  if(array_key_exists($prov_id, $city_dupe_name)):
    $dupe_ids = $city_dupe_name[$prov_id];

    foreach($data as $id => &$value) {
      if(in_array($id, $dupe_ids)) {
        $value['city_name'] = $value['city_name'] . ' (' . $value['type'] . ')';
      }
    }
  endif;

  return $data;
}