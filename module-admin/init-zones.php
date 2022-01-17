<?php
/**
 * Zone setting for Indo Shipping
 */
class WCIS_Zones_Method extends WC_Shipping_Method {
  private $api;
  private $main_settings;

  public function __construct($instance_id = 0) {
		$this->id = 'wcis_zone';
    $this->instance_id = absint($instance_id);

    $this->title = __('Indo Shipping');
		$this->method_title = __('Indo Shipping');
    $this->method_description = __('Indonesian domestic shipping with JNE, TIKI, or POS');
    $this->supports = array('shipping-zones', 'instance-settings',);

    // global
    $this->main_settings = get_option('woocommerce_wcis_settings');
    $this->api = new RajaOngkir($this->main_settings['key']);

    // allow save setting
    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
	}

  /**
   * Calculate_shipping function.
   * 
   * @param mixed $package
   */
	function calculate_shipping($package = []) {
    // if district not exists or empty
    $id_exists = array_key_exists('destination_id', $package['destination']);
    if(!$id_exists || empty($package['destination']['destination_id'])) {
      return false;
    }

    $costs = $this->_get_costs($package);
    $this->_set_rate($costs);
	}

  /////


  /**
   * Get the costs from selected Couriers.
   * 
   * @param array $package - The shipping detail
   * @return array - List of cost grouped by each courier
   */
  private function _get_costs($package) {
    $weight = $this->_calculate_weight($package);
    $selected_couriers = $this->_get_selected_couriers();

    $args = [
      'origin' => $this->main_settings['city'],
      'originType' => 'city',
      'destination' => $package['destination']['destination_id'],
      'destinationType' => 'subdistrict',
      'weight' => $weight,
      'courier' => $selected_couriers
    ];

    // get the cost
    $costs = $this->api->get_costs($args);
    return $costs;
  }

  /**
   * Set the Rate based on Cost list from API
   * 
   * @param array $costs - Cost list from API
   */
  private function _set_rate($costs) {
    if(!$costs) { return; }

    // format the costs from API to WooCommerce
    foreach($costs as $courier):
      if(empty($courier)) { break; }

      // get full list of services
      $code = $courier['code'];
      $all_services = wcis_get_services($code);

      // get allowed service from this courier
      $setting_id = $code . '_services';
      $allowed_services = isset($this->main_settings[$setting_id]) ? $this->main_settings[$setting_id] : array();

      foreach($courier['costs'] as $service):
        // check if this service is allowed
        $is_allowed = false;
        foreach($allowed_services as $as) {
          // if has variation
          if(isset($all_services[$as]['vars'])) {
            $is_allowed = in_array($service['service'], $all_services[$as]['vars']);
          }
          else {
            $is_allowed = $service['service'] === $as;
          }

          if($is_allowed) { break; }
        }

        if($is_allowed) {
          $rate = array(
            'id' => $code . '_' . strtolower($service['service']) . $this->instance_id,
            'label' => strtoupper($code) . ' ' . $service['service'],
            'cost' => $service['cost'][0]['value'],
            'calc_tax' => 'per_order'
          );

          $this->add_rate($rate);
        }
      endforeach;
    endforeach;
  }

  /**
   * Calculate the weight of items in cart. If all items has no weight specified, it will return 1.
   * 
   * @param array $package - POST parameter
   * @return int - THe weight in the unit specified in admin.
   */
  private function _calculate_weight($package) {
    global $woocommerce;
    $weight = wc_get_weight($woocommerce->cart->cart_contents_weight, 'g');

    // calculate volume
    // @warn - Setting default to "1" can cause item to be much larger if unit is set to "inch"
    $volume = array_reduce($woocommerce->cart->get_cart_contents(), function($result, $item) {
      $product = $item['data'];
      $length = (int) $product->get_length() ?? 1;
      $width = (int) $product->get_width() ?? 1;
      $height = (int) $product->get_height() ?? 1;
      $result += $length * $width * $height;
      return $result;
    }, 0);

    $volume = wc_get_dimension($volume, 'cm');
    $weight_volume = $volume / 6; //@todo: make this formula into a setting

    // if volume is heavier than weight, use the volume
    $weight = $weight_volume > $weight ? $weight_volume : $weight;

    if($weight > 0) {
      return $weight;
    }
    // if no weight data, return default weight or 1kg
    else {
      $weight = (int) ceil(apply_filters('wcis_default_weight', 1000));
      return $weight;
    }
  }

  /*
    Get selected services from the courier

    @return string - The courier format accepted by RajaOngkir, separated by semicolon (jne:tiki:pos)
  */
  private function _get_selected_couriers() {
    $couriers = wcis_get_couriers();

    $selected_couriers = [];
    foreach($couriers as $id => $name) {
      if(!empty($this->main_settings[$id . '_services'])) {
        $selected_couriers[] = $id;
      }
    }

    return join(':', $selected_couriers);
  }
}
