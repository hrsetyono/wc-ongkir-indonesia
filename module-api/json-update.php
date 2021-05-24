<?php
/**
 * Warning: Only `require` this file if you want to refresh the JSON data inside /json directory
 */

if(!defined('ABSPATH') ) { exit; } // exit if accessed directly

add_action( 'admin_init', 'wcis_refresh_json_data' );


/**
 * Refresh the JSON data with latest City and Subdistricts
 */
function wcis_refresh_json_data() {
  $prov = wcis_get_provinces();

  foreach( $prov as $code => $id ) {
    $result = wp_remote_get( WCIS_API . "/all-cities/$id" );

    // if not error
    if( !is_wp_error( $result ) ) {
      $data = $result['body'];
      file_put_contents( __DIR__ . "/json/city-$id.json", $data );
      break;
    }
  }
}