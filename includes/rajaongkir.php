<?php

if (!class_exists('RajaOngkir')):

/**
 * Handle API Call to RajaOngkir Server
 */
class RajaOngkir {
  private $api_key;
  private $base_url = 'https://pro.rajaongkir.com/api';

  function __construct($key = null) {
    // set API key
    if ($key) {
      $this->api_key = $key;
    }
    else {
      $cached_license = get_transient('ongkir_license');
      $this->api_key = $cached_license['key'];
    }
  }


  /**
   * Test if API key is working
   * 
   * @return bool
   */
  function is_valid() {
    $result = $this->get('/province', [], false);
    $status =  wp_remote_retrieve_response_code($result);
    return $status === 200;
  }


  /**
   * Get shipping costs
   * 
   * @param array $args - Formatted argument ready for API call
   * @return array - JSON response
   */
  function get_costs($args) {
    $result = $this->post('/cost', $args);

    if ($result) {
      $costs = $result['rajaongkir']['results'] ?? null;
      return $costs;
    } else {
      return $result;
    }
  }

  /////


  /**
   * GET request
   * 
   * @param string $endpoint
   * @param array $args - The URL params
   * @param boolean $return_only_body
   * 
   * @since 2.1.0
   */
  function get($endpoint, $args = [], $return_only_body = true) {
    $url = $this->get_endpoint_url($endpoint);

    if (!empty($args)) {
      $url = sprintf('%s?%s', $url, http_build_query($args));
    }

    $result = wp_remote_get($url, [
      'headers' => [ 'key' => $this->api_key ],
      'sslverify' => WP_DEBUG === true ? false : true,
    ]);

    if (is_wp_error($result)) {
      return $result->get_error_message();
    }

    if ($return_only_body) {
      return json_decode($result['body'], true);
    }

    return $result;
  }

  /**
   * POST request
   * 
   * @param string $endpoint
   * @param array $args - The URL params
   * @param boolean $return_only_body
   * 
   * @since 2.1.0
   */
  function post($endpoint, $args = [], $return_only_body = true) {
    $url = $this->get_endpoint_url($endpoint);

    $result = wp_remote_post($url, [
      'headers' => [ 'key' => $this->api_key ],
      'body' => $args,
      'sslverify' => WP_DEBUG === true ? false : true,
    ]);

    if (is_wp_error($result)) {
      return $result->get_error_message();
    }

    if ($return_only_body) {
      return json_decode($result['body'], true);
    }

    return $result;
  }

  /**
   * if URL doesn't start with "http", prepend base URL
   * 
   * @since 2.1.0
   */
  private function get_endpoint_url($endpoint) {
    $is_full_url = preg_match('/^http/', $endpoint, $matches);
    $url = $is_full_url ? $endpoint : sprintf('%s%s', $this->base_url, $endpoint);

    return $url;
  }



  /**
   * GET request using Curl
   * 
   * @deprecated 2.1.0 - Use wp_remote_get() instead
   * 
   * @param string $endpoint
   * @param array $args - The URL params
   */
  function get_curl($endpoint, $args = []) {
    $url = $this->get_endpoint_url($endpoint);
 
    if (!empty($args)) {
      $url = sprintf('%s?%s', $url, http_build_query($args));
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'key:' . $this->api_key,
    ]);

    // If localhost testing
    if (WP_DEBUG) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
  }

  /**
   * POST request using Curl
   * 
   * @deprecated 2.1.0 - Use wp_remote_post() instead
   * 
   * @param string $endpoint
   * @param array $body
   */
  function post_curl($endpoint, $body = []) {
    $url = $this->get_endpoint_url($endpoint);
    $payload = json_encode($body);

    // Prepare new cURL resource
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
    // Set HTTP Header for POST request 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'key:' . $this->api_key,
      'Content-Type: application/json',
      'Content-Length: ' . strlen($payload)
    ]);
    
    // Submit the POST request
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }
}

endif;