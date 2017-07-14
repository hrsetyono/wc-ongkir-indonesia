<?php
/*
  Handle request for static data files

  TODO: add postal code range for all cities, then we can use Postal Code field for city detection.
*/
class WCIS_Data {
  /*
    Translate Province Code into ID based on Raja Ongkir list.

    @param str $state - State / Province code from WooCommerce
    @return int - the province's ID
  */
  static function get_province_id($state) {
    $provinces = self::_get_json_file('provinces.json');
    $id = array_key_exists($state, $provinces) ? $provinces[$state] : 0;
    return $id;
  }

  /*
    Get all cities in the provice

    @param $prov_id - ID of the province
    @retun array
  */
  static function get_cities($prov_id) {
    $data = self::_get_json_file('cities/' . $prov_id . '.json');

    // if exists, filter and return it
    if($data) {
      $data = self::_filter_dupe_name($prov_id, $data);
      return $data;
    } else {
      return $data;
    }
  }

  /*
    Get all couriers.

    @return array - List of couriers in (slug => name) format.
  */
  static function get_couriers() {
    $couriers_raw = self::_get_json_file('couriers.json');

    // remap
    $couriers = array();
    foreach($couriers_raw as $key => $value) {
      $couriers[$key] = $value['name'];
    }

    return $couriers;
  }

  /*
    Get the services provided by the courier.

    @param string $name - Courier slug as listed in couriers.json
    @param bool $simple_format - *Optional*. If true, return a simplified `id => name` format. Default is false.
    @return array - The services this courier provided
  */
  static function get_services($name, $simple_format = false) {
    if($name === 'J&T') { $name = 'jnt'; } // for weird reason, the response code for 'jnt' is 'J&T'

    $couriers = self::_get_json_file('couriers.json');
    $courier = isset($couriers[$name]) ? $couriers[$name] : null;

    // if courier found
    if($courier) {
      $services = $courier['services'];

      // simplify the data
      if($simple_format) {
        $parsed = array();
        foreach($courier['services'] as $key => $val) {
          $parsed[$key] = $val['title'];
        }

        $services = $parsed;
      }

      return $services;
    }
  }

  /*
    Get the district that isn't in JNE

    TODO: not used

    @param int $city_id
    @return array - The city list
  */
  static function get_jne_district_exc($city_id) {
    return array_key_exists($city_id, self::JNE_DISTRICT_EXC) ? self::JNE_DISTRICT_EXC[$city_id] : null;
  }


  /////


  /*
    Get JSON file inside /data directory

    @param string $filename
    @return array
  */
  private static function _get_json_file($filename) {
    $fileurl = WCIS_DIR . "/data/$filename";
    $args = array(
      'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
      ),
    );

    $fileraw = file_get_contents($fileurl, false, stream_context_create($args) );
    return json_decode($fileraw, true);
  }

  const JNE_DISTRICT_EXC = array(
    '63' => array(
      '842' // bunga mas
    ),
  );

  /*
    Add prefix to city and district that have the same name

    @param array $data - Cities data
    @param array $dupe_ids - ID of cities that share the same name

    @return array - Prefixed list
  */
  private static function _filter_dupe_name($prov_id, $data) {
    // Province that has same city and district name
    $city_dupe_name = array(
      3 => array(402, 403, 455, 456), // Serang, Tangerang
      9 => array(22, 23, 54, 55, 78, 79, 108, 109, 430, 431, 468, 469), // Bandung, Bekasi, Bogor, Cirebon, Sukabumi, Tasikmalaya
      10 => array(249, 250, 348, 349, 472, 473, 398, 399), // Magelang, Pekalongan, Tegal, Semarang
      11 => array(74, 75, 178, 179, 255, 256, 247, 248, 289, 290, 342, 343, 369, 370), // Blitar, Kediri, Malang, Madiun, Mojokerto, Pasuruan, Probolinggo,

      32 => array(420, 421), // Solok

      22 => array(68, 69), // Bima
      23 => array(212, 213), // Kupang

      12 => array(364, 365), // Pontianak
      7 => array(129, 130), // Gorontalo

      24 => array(157, 158), // Jayapura
      25 => array(424, 425), // Sorong
    );

    // if province has duplicate city name
    if(array_key_exists($prov_id, $city_dupe_name) ):
      $dupe_ids = $city_dupe_name[$prov_id];

      foreach($data as $id => &$value) {
        if(in_array($id, $dupe_ids) ) {
          $value['city_name'] = $value['city_name'] . ' (' . $value['type'] . ')';
        }
      }
    endif;

    return $data;
  }
}
