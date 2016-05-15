<?php
/*
  Translate WooCommerce Province code into Raja Ongkir
*/
class WCIS_Data {
  /*
    Translate Prov Code into ID based on Raja Ongkir list.

    @param string $code - province code from WooCommerce
    @return int - the province's ID
  */
  static function get_province_id($code) {
    $provinces = self::$PROVINCES; // dereference for php < 5.3
    $id = array_key_exists($code, $provinces) ? $provinces[$code] : 0;
    return $id;
  }

  /*
    Get all couriers.

    @return array - List of couriers in (slug => name) format.
  */
  static function get_couriers() {
    return self::$COURIERS;
  }

  /*
    Get the services provided by the courier.

    @param string $courier - Courier slug as listed in self::COURIERS
    @return array - The services this courier provided
  */
  static function get_services($courier) {
    $services = self::$SERVICES;
    return array_key_exists($courier, $services) ? $services[$courier] : array();
  }

  /////

  static $PROVINCES = array(
    'AC' => 21, 'SU' => 34,
    'SB' => 32, 'RI' => 26,
    'KR' => 17, 'JA' => 8,
    'SS' => 28, 'BB' => 2,
    'BE' => 4, 'LA' => 18,

    // jawa
    'JK' => 6, 'JB' => 9,
    'BT' => 3, 'JT' => 10,
    'JI' => 11, 'YO' => 5,

    // nusa tenggara
    'BA' => 1, 'NB' => 22,
    'NT' => 23,

    // kalimantan
    'KB' => 12, 'KT' => 14,
    'KI' => 15, 'KS' => 13,
    'KU' => 16,

    // sulawesi
    'SA' => 31, 'ST' => 29,
    'SG' => 30, 'SR' => 27,
    'SN' => 28, 'GO' => 7,

    // maluku
    'MA' => 19, 'MU' => 20,

    // papua
    'PA' => 24, 'PB' => 25,
  );

  static $COURIERS = array(
    'jne' => 'JNE',
    'tiki' => 'TIKI',
    'pos' => 'POS Indonesia'
  );

  static $SERVICES = array(
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
      'ECO' => 'ECO - Economi Service',
      'REG' => 'REG - Reguler Service',
      'ONS' => 'ONS - Over Night Service',
      'HDS' => 'HDS - Holiday Delivery Service',
      'SDS' => 'SDS - Same Day Service'
    ),

    'pos' => array(
      'Surat Kilat Khusus' => 'Surat Kilat Khusus',
      'Express Next Day' => 'Express Next Day'
    )
  );
}
