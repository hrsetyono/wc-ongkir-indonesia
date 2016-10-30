<?php
/*
  Handle the getter for static JSON file provided in /data directory
*/
class WCIS_Data {
  /*
    Translate Prov Code into ID based on Raja Ongkir list.

    @param string $code - province code from WooCommerce
    @return int - the province's ID
  */
  static function get_province_id($code) {
    $provinces = self::get_json_file('provinces.json');
    $id = array_key_exists($code, $provinces) ? $provinces[$code] : 0;
    return $id;
  }

  /*
    Get all couriers.

    @return array - List of couriers in (slug => name) format.
  */
  static function get_couriers() {
    $couriers_raw = self::get_json_file('couriers.json');

    // remap
    $couriers = array();
    foreach($couriers_raw as $key => $value) {
      $couriers[$key] = $value['name'];
    }

    return $couriers;
  }

  /*
    Get the services provided by the courier.

    @param $name (str) - Courier slug as listed in couriers.json
    @param $simple_format (bool) - *Optional* If true, return a simplified `id => name` format. Default is false.
    @return array - The services this courier provided
  */
  static function get_services($name, $simple_format = false) {
    $couriers = self::get_json_file('couriers.json');
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
  */
  static function get_jne_district_exc($city_id) {
    return array_key_exists($city_id, self::JNE_DISTRICT_EXC) ? self::JNE_DISTRICT_EXC[$city_id] : null;
  }


  /////


  /*
    Get JSON file inside /data directory
  */
  private static function get_json_file($filename) {
    return json_decode(file_get_contents(WCIS_DIR . "/data/$filename"), true);
  }


  const JNE_DISTRICT_EXC = array(
    '63' => array(
      '842' // bunga mas
    ),
  );
}
