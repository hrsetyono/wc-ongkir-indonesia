<?php
/*
  Zone setting for Indo Shipping
*/
class WCIS_Zones_Method extends WC_Shipping_Method {
  private $api;
  private $main_settings;

  public function __construct($instance_id = 0) {
		$this->id = 'wcis_zone';
    $this->instance_id = absint($instance_id);

    $this->title = __('Indo Shipping', 'wcis');
		$this->method_title = __('Indo Shipping', 'wcis');
    $this->supports = array('shipping-zones');

    // global
    $this->main_settings = get_option('woocommerce_wcis_settings');
    $this->api = new WCIS_API($this->main_settings['key']);

    // allow save setting
    add_action('woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/*
	  Calculate_shipping function.

	  @param mixed $package
	*/
	function calculate_shipping($package = array() ) {
    // if district not exists or empty
    $id_exists = array_key_exists('destination_id', $package['destination']);
    if(!$id_exists || empty($package['destination']['destination_id']) ) {
      return false;
    }

    $costs = $this->_get_costs($package);
    $this->_set_rate($costs);
	}

  /////


  /*
    Get the costs from selected Couriers.

    @param array $package - The shipping detail
    @return array - List of cost grouped by each courier
  */
  private function _get_costs($package) {
    $weight = $this->_calculate_weight($package);
    $selected_couriers = $this->_get_selected_couriers();

    // format the args to be suitable for API
    $args = array();
    foreach($selected_couriers as $courier) {
      $args[] = array(
        'origin' => $this->main_settings['city'],
        'originType' => 'city',
        'destination' => $package['destination']['destination_id'],
        'destinationType' => 'subdistrict',
        'weight' => $weight,
        'courier' => $courier
      );
    }

    // get the cost of each couriers
    $couriers_cost = array();
    foreach($args as $a) {
      $couriers_cost[] = $this->api->get_costs($a);
    }

    return $couriers_cost;
  }

  /*
    Set the Rate based on Cost list from API

    @param array $couriers_cost - Cost list from API
  */
  private function _set_rate($couriers_cost) {
    // format the costs from API to WooCommerce
    foreach($couriers_cost as $courier):

      if(empty($courier) ) { break; }

      // get full list of services
      $code = $courier[0]['code'];
      $all_services = WCIS_Data::get_services($code);

      // get allowed service from this courier
      $setting_id = $code . '_services';
      $allowed_services = isset($this->main_settings[$setting_id]) ? $this->main_settings[$setting_id] : array();

      foreach($courier[0]['costs'] as $service):

        // check if this service is allowed
        $is_allowed = false;
        foreach($allowed_services as $as) {
          // if has variation
          if(isset($all_services[$as]['vars']) ) {
            $is_allowed = in_array($service['service'], $all_services[$as]['vars'] );
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

  /*
    Calculate the weight of items in cart. If all items has no weight specified, it will return 1.

    @param array - POST parameter
    @return int - THe weight in the unit specified in admin.
  */
  private function _calculate_weight($package) {
    global $woocommerce;

    $weight = $woocommerce->cart->cart_contents_weight;

    if($weight > 0) {
      return $weight;
    }
    // if no weight data, return default weight or 1kg
    else {
      $weight = (int) ceil(apply_filters('wcis_default_weight', $package) );

      return (is_int($weight) && $weight > 0 ) ? $weight : 1;
    }
  }

  /*
    Get selected services from the courier

    @return array
  */
  private function _get_selected_couriers() {
    $couriers = WCIS_Data::get_couriers();

    $selected_couriers = array();
    foreach($couriers as $id => $name) {
      if(!empty($this->main_settings[$id . '_services']) ) {
        $selected_couriers[] = $id;
      }
    }

    return $selected_couriers;
  }

}
