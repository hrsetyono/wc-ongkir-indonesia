<?php

class WCIS_API {
  private $api_key;
  private $api_base = 'http://pro.rajaongkir.com/api';

  const PROVINCE_URL = '/province';
  const CITY_URL = '/city?province=';
  const DISTRICT_URL = '/subdistrict?city=';
  const COST_URL = '/cost';

  function __construct($api_key = '') {
    $this->api_key = ($api_key) ? $api_key : $this->api_key;
  }

  /*
    Test if API key is working
  */
  function is_valid() {
    $response = $this->call(self::PROVINCE_URL . '?id=6');
    return ($response['status']['code'] === 200);
  }

  /*
    Get all province data

    @return array - All
  */
  function get_provinces() {
    $response = $this->call(self::PROVINCE_URL);
    return $response['results'];
  }

  /*
    Get all cities in the provice

    @param $prov_id - ID of the province
    @retun array - city data
  */
  function get_cities($prov_id) {
    $response = $this->call(self::CITY_URL . $prov_id);

    if($response['status']['code'] === 200) {
      $cities = $response['results'];

      switch($prov_id) {
        case 9:
          $cities = $this->diff_city_name($cities, 'Bandung');
          break;
        case 10:
           $cities = $this->diff_city_name($cities, 'Semarang');
           break;
      }

      return $cities;
    } else {
      return $response;
    }
  }

  /*
    Get all districts in the city

    @param int $city_id
    @param array $couriers - Selected couriers
  */
  function get_districts($city_id, $couriers) {
    $response = $this->call(self::DISTRICT_URL . $city_id);

    if($response['status']['code'] === 200) {
      $results = $response['results'];

      // if only courier is JNE, Filter the district that isn't available in JNE
      // if(count($couriers) === 1 && $couriers[0] === 'jne') {
      //   $exception = WCIS_Data::get_jne_district_exc($city_id);
      //
      //   if($exception) {
      //     $results = array_filter($results, function($r) {
      //       $dist_id = (int) $r['subdistrict_id'];
      //       return !in_array($dist_id, $exception);
      //     });
      //   }
      // }

      return $results;
    } else {
      return $response;
    }
  }

  /*
    Get costs
  */
  function get_costs($args) {
    $query = http_build_query($args);
    $response = $this->call(self::COST_URL, array(
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $query,
      CURLOPT_HTTPHEADER => array(
        'content-type: application/x-www-form-urlencoded',
        'key: ' . $this->api_key
      ),
    ));

    if($response) {
      $costs = $response['results'];
      return $costs;
    } else {
      return $response;
    }
  }

  /////

  /*
    Call API
  */
  private function call($endpoint, $extra_options = array() ) {
    $curl = curl_init();
    $curl_options = array(
      CURLOPT_URL => $this->api_base . $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'key: ' . $this->api_key
      ),
    );

    foreach($extra_options as $option => $value) {
      $curl_options[$option] = $value;
    }

    curl_setopt_array($curl, $curl_options);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    $response = json_decode($response, true)['rajaongkir'];

    return $response;
  }

  /*
    If there's City and District with the same name
  */
  private function diff_city_name($cities, $city_name) {
    $cities = array_map(function($c) use ($city_name) {
      if($c['city_name'] === $city_name) {
        $c['city_name'] = $c['type'] . ' ' . $c['city_name'];
      }
      return $c;
    }, $cities);

    return $cities;
  }

}
