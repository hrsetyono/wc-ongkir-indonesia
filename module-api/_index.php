<?php

require_once WCIS_DIR . '/helper/rajaongkir.php';
require_once __DIR__ . '/static.php';

add_action('rest_api_init', 'wcis_init_api');

/**
 * @action rest_api_init
 */
function wcis_init_api() {
  // STATIC
  register_rest_route(WCIS_NAMESPACE, '/cities/(?P<prov_code>\w+)/', [
    'methods' => 'GET',
    'callback' => 'wcis_get_cities_api',
    'permission_callback' => '__return_true'
  ]);

  register_rest_route(WCIS_NAMESPACE, '/districts/(?P<prov_code>\w+)/(?P<city_id>\d+)/', [
    'methods' => 'GET',
    'callback' => 'wcis_get_districts_api',
    'permission_callback' => '__return_true'
  ]);

  register_rest_route(WCIS_NAMESPACE, '/fields/(?P<type>\w+)/(?P<prov_code>\w+)/(?P<district_id>\d+)', [
    'methods' => 'GET',
    'callback' => 'wcis_get_fields_api',
    'permission_callback' => '__return_true'
  ]);
}