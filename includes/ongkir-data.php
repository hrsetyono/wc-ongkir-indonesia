<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('Ongkir_Data')):

class Ongkir_Data {
  /**
   * Get all cities in a province
   */
  static function get_cities($prov_code, $raw = false) {
    // if provided param is ID, convert to code
    if (is_int($prov_code)) {
      $prov_code = self::get_province_code($prov_code);
    }

    $prov_code = strtoupper($prov_code);
    $data = self::_read_json($prov_code . '.json');
    return $data;
  }

  /**
   * Convert ID to Province Code based on RajaOngkir list.
   */
  static function get_province_code($prov_id) {
    $provs = self::_read_json('provinces.json');
    $code = array_search($prov_id, $provs);
    return $code;
  }

  /**
   * Translate Province Code into ID based on Raja Ongkir list.
   * 
   * @param str $state - State / Province code from WooCommerce
   * @return int - the province's ID
   */
  static function get_province_id($prov_code) {
    $provs = self::_read_json('provinces.json');
    $id = array_key_exists($prov_code, $provs) ? $provs[$prov_code] : 0;
    return $id;
  }

  /**
   * Get all provinces in Code:ID format. Code is the abbreviation from WooCommerce, ID is the number from RajaOngkir
   */
  static function get_provinces() {
    $provinces = self::_read_json('provinces.json');
    return $provinces;
  }

  /**
   * Get all couriers.
   * 
   * Available: pos,tiki,jne,pcp,esl,rpx,pandu,wahana,jnt,pahala,cahaya, sap,jet,indah,dse,slis,first,ncs,star
   * 
   * @return array - List of couriers in (slug => name) format.
   */
  static function get_couriers() {
    $couriers_raw = self::_read_json('couriers.json');

    // remap
    $couriers = [];
    foreach ($couriers_raw as $key => $value) {
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
  static function get_services($name, $simple_format = false) {
    $couriers = self::_read_json('couriers.json');
    $courier = isset($couriers[$name]) ? $couriers[$name] : null;

    // if courier found
    if ($courier) {
      $services = $courier['services'];

      // simplify the data
      if ($simple_format) {
        $parsed = [];
        foreach ($courier['services'] as $key => $val) {
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
  private static function _read_json($filename) {
    $path = ONGKIR_DIR . "/includes/data/{$filename}";

    if (!file_exists($path)) {
      die('The data file does not exist');
    }

    $data = wp_json_file_decode($path, ['associative' => true]);
    return $data;
  }

  /**
   * Add prefix to city and district that have the same name
   * 
   * @param array $data - Cities data
   * @param array $dupe_ids - ID of cities that share the same name
   * @return array - Prefixed list
   */
  private static function _prefix_dupe_city_name($prov_id, $data) {
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
    if (array_key_exists($prov_id, $city_dupe_name)):
      $dupe_ids = $city_dupe_name[$prov_id];

      foreach ($data as $id => &$value) {
        if (in_array($id, $dupe_ids)) {
          $value['city_name'] = $value['city_name'] . ' (' . $value['type'] . ')';
        }
      }
    endif;

    return $data;
  }
}

endif;