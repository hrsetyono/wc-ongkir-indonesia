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
    $provinces = json_decode(file_get_contents(WCIS_DIR . '/data/provinces.json'), true);
    $id = array_key_exists($code, $provinces) ? $provinces[$code] : 0;
    return $id;
  }

  /*
    Get all couriers.

    @return array - List of couriers in (slug => name) format.
  */
  static function get_couriers() {
    return self::COURIERS;
  }

  /*
    Get the services provided by the courier.

    @param $courier (str) - Courier slug as listed in self::COURIERS
    @param $simple_format (bool) - *Optional* If true, return a simplified `id => name` format. Default is false.
    @return array - The services this courier provided
  */
  static function get_services($courier, $simple_format = false) {
    $services_raw = array_key_exists($courier, self::SERVICES) ? self::SERVICES[$courier] : array();

    if($simple_format) {
      $services = array();
      foreach($services_raw as $key => $val) {
        $services[$key] = $val['title'];
      }
      return $services;
    }
    else {
      return $services_raw;
    }
  }

  /*
    Get the district that isn't in JNE
  */
  static function get_jne_district_exc($city_id) {
    return array_key_exists($city_id, self::JNE_DISTRICT_EXC) ? self::JNE_DISTRICT_EXC[$city_id] : null;
  }

  /////

  const COURIERS = array(
    'jne' => 'JNE',
    'tiki' => 'TIKI',
    'pos' => 'POS Indonesia'
  );

  const SERVICES = array(
    'jne' => array(
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
    ),

    'tiki' => array(
      'ECO' => array(
        'title' => 'ECO - Economi Service',
      ),
      'REG' => array(
        'title' => 'REG - Reguler Service',
      ),
      'ONS' => array(
        'title' => 'ONS - Over Night Service',
      ),
      'HDS' => array(
        'title' => 'HDS - Holiday Delivery Service',
      ),
      'SDS' => array(
        'title' => 'SDS - Same Day Service'
      ),
    ),

    'pos' => array(
      'Surat Kilat Khusus' => array(
        'title' => 'Surat Kilat Khusus'
      ),
      'Express Next Day' => array(
        'title' => 'Express Next Day'
      ),
    )
  );

  const JNE_DISTRICT_EXC = array(
    '63' => array(
      '842' // bunga mas
    ),
  );
}
