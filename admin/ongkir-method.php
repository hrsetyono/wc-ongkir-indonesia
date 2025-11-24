<?php
if (!defined('ABSPATH')) { exit; }


if (!class_exists('Ongkir_Method')):

/**
 * Global setting for Indo Shipping
 */
class Ongkir_Method extends WC_Shipping_Method {
  private $api;

  public function __construct($instance_id = 0) {
		$this->id = 'ongkir';
    $this->title = __('Ongkir Indonesia', 'wc-ongkir-indonesia');
		$this->method_title = __('Ongkir Indonesia', 'wc-ongkir-indonesia');
		$this->method_description = __('Indonesian domestic shipping with JNE, TIKI, Ninja Xpress, Sicepat, or POS', 'wc-ongkir-indonesia');

    $this->enabled = $this->get_option('enabled');

    // allow save setting
    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);

    $this->init_form_fields();
	}

  /**
   * Initiate global setting page
   */
  function init_form_fields() {
    $this->form_fields = [
      'key' => [
        'title' => 'API Key',
        'type' => 'password',
        'description' => __('Signup at <a href="https://collaborator.komerce.id/registration" target="_blank">rajaongkir.com</a> (Free Starter plan works). Paste the API Key here. <br> ⚠️ <span style="color:red">If nothing happen after saving, try refreshing the page as it takes a while to validate the key.</span>', 'wc-ongkir-indonesia'),
      ],
    ];

    // if key is valid, show the other setting fields
    if ($this->check_key_valid()) {
      $this->form_fields['enabled'] = [
        'title' => __('Enable/Disable', 'wc-ongkir-indonesia'),
        'type' => 'checkbox',
        'label' => __('Enable Indo Shipping', 'wc-ongkir-indonesia'),
        'description' => __( 'Tick this then go to Shipping Zone > Create Indonesia Zone > Add Shipping Method > Choose "Indo Shipping"', 'wc-ongkir-indonesia'),
        'default' => 'yes'
      ];

      // format city field
      $city_field = [
        'title' => __('City Origin', 'wc-ongkir-indonesia'),
        'type' => 'select',
        // 'class'    => 'wc-enhanced-select', // bugged!! doesn't save the value
        'description' => sprintf(__('Ship from where? Change your province <a href="%s">here</a>', 'wc-ongkir-indonesia'), admin_url('admin.php?page=wc-settings&tab=general')),
        'options' => [],
      ];
      $city_field['options'] = $this->get_cities_origin();

      if (is_wp_error($city_field['options'])) {
        $city_field['description'] .= '<br><span style="color:red">' . $city_field['options']->get_error_message() . '</span>';
        $city_field['options'] = [0 => __('-- No Cities Available --', 'wc-ongkir-indonesia')];
      }

      $this->form_fields['city'] = $city_field;

      // set service fields by each courier
      $couriers = Ongkir_Data::get_couriers();

      foreach ($couriers as $id => $name) {
        $this->form_fields[$id . '_services'] = [
          'title' => $name,
          'type' => 'multiselect',
          'class' => 'wc-enhanced-select',
          'description' => __("Choose allowed services by $name.", 'wc-ongkir-indonesia'),
          'options' => Ongkir_Data::get_services($id, true)
        ];
      }
    }
  }


  /**
   * Add API Key to Option so it's cached
   */
  function process_admin_options() {
    $cached_license = get_option('ongkir_license');

    $post_data = $this->get_post_data();
    $key = $post_data['woocommerce_ongkir_key'];

    // check license
    $license_different = isset($cached_license['key']) && $cached_license['key'] === $key;
    $license_valid = isset($cached_license['valid']) && $cached_license['valid'];

    // if not valid OR different from before, update option
    if ($license_different || !$license_valid) {
      $rj = new RajaOngkir($key);
      $license = [
        'key' => $key,
        'valid' => $rj->is_valid()
      ];

      update_option('ongkir_license', $license);
    }

    return parent::process_admin_options();
  }

  /////


  /**
   * Validate API Key by doing a sample AJAX call
   * @return bool
   * 
   * @warn - due to woocommerce update, the option isn't saved when the page first refreshed. So need to refresh the page again
   */
  private function check_key_valid() {
    $license = get_option('ongkir_license');
    
    // if key doesn't exist OR empty, abort
    if (!isset($license['key']) || $license['key'] === '') {
      return false;
    }

    // if valid, return success
    if (isset($license['valid']) && $license['valid']) {
      $msg = __('API Connected!', 'wc-ongkir-indonesia');
      $this->form_fields['key']['description'] = '<span style="color: #4caf50;">' . $msg . '</span>';
    }
    else {
      $msg = __('Invalid API Key.', 'wc-ongkir-indonesia');
      $this->form_fields['key']['description'] = '<span style="color:red;">' . $msg . '</span>';
    }

    return $license['valid'];
  }

  /**
   * Get cities list from cache. Cached when the setting is saved.
   * @return array - List of cities in base province
   */
  private function get_cities_origin() {
    $country = wc_get_base_location();
    $prov_code = $country['state'];

    if (!$prov_code) {
      return new WP_Error('no_province', __('⚠️ Please set your province first', 'wc-ongkir-indonesia'));
    }

    $cities_raw = Ongkir_Data::get_cities($prov_code);

    $cities = [];
    foreach ($cities_raw as $c) {
      $cities[$c['id']] = $c['name'];

      foreach ($c['districts'] as $d) {
        $cities[$d['id']] = '— ' . ucwords(strtolower($d['name']));
      }
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