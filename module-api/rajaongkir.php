<?php

/**
 * Handle API Call to RajaOngkir Server
 */
class RajaOngkir {
  private $api_key;

  function __construct( $key = null ) {
    // set API key
    if( $key ) {
      $this->api_key = $key;
    }
    else {
      $cached_license = get_transient( 'wcis_license' );
      $this->api_key = $cached_license['key'];
    }
  }


  /**
   * Test if API key is working
   * 
   * @return bool
   */
  function is_valid() {
    $result = $this->call_rajaongkir( '/province', 'GET' );

    $status =  wp_remote_retrieve_response_code( $result );
    return $status === 200;
  }


  /**
   * Get shipping costs
   * 
   * @param array $args - Formatted argument ready for API call
   * @return array - JSON response
   */
  function get_costs( $args ) {
    $result = $this->call_rajaongkir( '/cost', 'POST', $args );

    if( $result ) {
      $costs = $result['rajaongkir']['results'] ?? null;
      return $costs;
    } else {
      return $result;
    }
  }

  /////

  /**
   * Call API
   * 
   * @param string $endpoint
   * @param array $args - *Optional*. Additional arguments
   * @return array - JSON response
   */
  private function call_rajaongkir( $endpoint, $method = 'GET', $args = [] ) {
    $result = null;

    // do the call
    if( $method == 'GET' ) {
      $result = wp_remote_get( RAJAONGKIR_API . $endpoint, [
        'headers' => [ 'key' => $this->api_key ]
      ] );
    }
    elseif( $method == 'POST' ) {
      $result = wp_remote_post( RAJAONGKIR_API . $endpoint, [
        'headers' => [ 'key' => $this->api_key ],
        'body' => $args
      ] );
    }

    // check for error
    if( is_wp_error( $result ) ) {
      return $result->get_error_message();
    }
    else {
      return json_decode( $result['body'], true );
    }
  }

}
