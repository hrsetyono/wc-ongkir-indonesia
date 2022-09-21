<?php
if (!defined('ABSPATH')) { exit; }

require_once ONGKIR_DIR . '/includes/rajaongkir.php';
require_once ONGKIR_DIR . '/includes/ongkir-data.php';


if (!class_exists('Ongkir_Method')):

/**
 * Global setting for Indo Shipping
 */
class Ongkir_Method extends WC_Shipping_Method {
  private $api;

  public function __construct($instance_id = 0) {
		$this->id = 'ongkir';
    $this->title = __('Ongkir Indonesia');
		$this->method_title = __('Ongkir Indonesia');
		$this->method_description = __('Indonesian domestic shipping with JNE, J&T, Ninja Xpress, Sicepat, TIKI, or POS');

    $this->enabled = $this->get_option('enabled');
    $this->init_form_fields();

    // allow save setting
    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_transients']);
	}

  /**
   * Initiate global setting page
   */
  function init_form_fields() {
    $enabled_field = [
      'title' => __('Enable/Disable'),
      'type' => 'checkbox',
      'label' => __('Enable Indo Shipping'),
      'description' => __( 'Tick this then go to Shipping Zone > Create Indonesia Zone > Add Shipping Method > Choose "Indo Shipping"' ),
      'default' => 'yes'
    ];

    // $type_field = [
    //   'title' => __('License Type'),
    //   'type' => '',
    //   'description' => __('Starter is free, but limited to JNE, TIKI, and POS'),
    //   'options' => [
    //     'pro' => 'Pro',
    //     'starter' => 'Starter',
    //   ],
    // ];

    $key_field = [
      'title' => __('API Key'),
      'type' => 'password',
      'description' => __('Signup at <a href="http://rajaongkir.com/akun/daftar" target="_blank">rajaongkir.com</a> and choose Pro license (Paid). Paste the API Key here. <br> ⚠️ <span style="color:#f44336">If nothing happen after saving, try refreshing the page as it takes a while to validate the key.</span>'),
    ];

    $city_field = [
      'title' => __('City Origin'),
      'type' => 'select',
      // 'class'    => 'wc-enhanced-select', // bugged!! doesn't save the value
      'description' => __('Ship from where? <br> Change your province at General > Store Address'),
      'options' => [],
    ];

    $this->form_fields = [
      'key' => $key_field,
    ];

    // if key is valid, show the other setting fields
    if ($this->check_key_valid()) {
      $city_field['options'] = $this->get_cities_origin();

      $this->form_fields['enabled'] = $enabled_field;
      $this->form_fields['city'] = $city_field;

      // set service fields by each courier
      $couriers = Ongkir_Data::get_couriers();

      foreach ($couriers as $id => $name) {
        $this->form_fields[$id . '_services'] = [
          'title' => $name,
          'type' => 'multiselect',
          'class' => 'wc-enhanced-select',
          'description' => __("Choose allowed services by $name."),
          'options' => Ongkir_Data::get_services($id, true)
        ];
      }
    }
  }


  /**
   * Add API Key to Transient so it's cached
   */
  function process_admin_transients() {
    $cached_license = get_transient('ongkir_license');

    $post_data = $this->get_post_data();
    $key = $post_data['woocommerce_ongkir_key'];

    // check license
    $license_different = isset($cached_license['key']) && $cached_license['key'] === $key;
    $license_valid = isset($cached_license['valid']) && $cached_license['valid'];

    // if not valid OR different from before, update transient
    if ($license_different || !$license_valid) {
      $rj = new RajaOngkir($key);
      $license = [
        'key' => $key,
        'valid' => $rj->is_valid()
      ];

      set_transient('ongkir_license', $license, 60*60*24*30);
      // return $license;
    }
  }


  /////


  /**
   * Validate API Key by doing a sample AJAX call
   * @return bool
   * 
   * @warn - due to woocommerce update, the transient isn't saved when the page first refreshed. So need to refresh the page again
   */
  private function check_key_valid() {
    $license = get_transient('ongkir_license');
    
    // if key doesn't exist OR empty, abort
    if (!isset($license['key']) || $license['key'] === '') {
      return false;
    }

    // if valid, return success
    if (isset($license['valid']) && $license['valid']) {
      $msg = __('API Connected!');
      $this->form_fields['key']['description'] = '<span style="color: #4caf50;">' . $msg . '</span>';
    }
    else {
      $msg = __('Invalid API Key. Make sure it is a PRO license');
      $this->form_fields['key']['description'] = '<span style="color:#f44336;">' . $msg . '</span>';
    }

    return $license['valid'];
  }

  /**
   * Get cities list from cache. Cached when the setting is saved.
   * @return array - List of cities in base province
   */
  private function get_cities_origin() {
    $country = wc_get_base_location();
    $prov_id = Ongkir_Data::get_province_id($country['state']);

    // get cities data
    $cities_raw = Ongkir_Data::get_cities($prov_id);

    // parse raw data
    $cities = [];
    foreach ($cities_raw as $id => $value) {
      $cities[$id] = $value['city_name'];
    }

    return $cities;
  }


  /**
   * Set API key to cache
   * 
   * @return array - in the format of `{ key, valid }`
   */
  private function _set_license_cache() {
    
  }
}

endif;