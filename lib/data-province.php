<?php

/*
  Translate WooCommerce Province code into Raja Ongkir
*/

class WCIS_Provinces {
  const PROVINCES = array(
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

  /*
    Translate Prov Code into ID based on Raja Ongkir list.

    @param string $code - province code from WooCommerce
    @return int - the province's ID
  */
  static function get_id($code) {
    $id = (array_key_exists($code, self::PROVINCES) ) ? self::PROVINCES[$code] : 0;
    return $id;
  }
}
