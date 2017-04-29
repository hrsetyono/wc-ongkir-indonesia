<?php

class WCIS_API {
  private $api_key;
  private $api_base = 'http://pro.rajaongkir.com/api';

  const PROVINCE_URL = '/province';
  const COST_URL = '/cost';

  function __construct($api_key = '') {
    $this->api_key = ($api_key) ? $api_key : $this->api_key;
  }

  /*
    Test if API key is working

    @return bool
  */
  function is_valid() {
    $response = $this->call(self::PROVINCE_URL . '?id=6');
    return ($response['status']['code'] === 200);
  }

  /*
    Get shipping costs

    @param array $args - Formatted argument ready for API call
    @return array - JSON response
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
      $costs = $response;
      return $costs;
    } else {
      return $response;
    }
  }

  /////


  /*
    Call API

    @param string $endpoint
    @param array $extra_options - *Optional*. Additional arguments
    @return array - JSON response
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

    $response = json_decode($response, true);
    return $response['rajaongkir'];
  }

}
