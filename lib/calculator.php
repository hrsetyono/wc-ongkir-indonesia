<?php
/*
  Add district to the Shipping calculator data

  @param mixed $packages - Cart parameters with products and destination data
*/
function wcis_cart_calculator($packages) {

  // if checkout page
  if(isset($_POST['security']) ) {
    parse_str($_POST['post_data'], $post_data);
    $packages[0]['destination']['destination_id'] = $post_data['billing_destination_id'];
  }

  // if cart page
  if(isset($_POST['calc_shipping_wcis_d']) ) {
    $packages[0]['destination']['destination_id'] = $_POST['calc_shipping_wcis_d'];
  }

  // var_dump($packages[0]['destination']);


  error_log(print_r($packages[0]['destination'], true) );
  // error_log(print_r($_POST, true) );

  // $packages[0]['destination']['district_id'] = 5498;

  return $packages;
}

// Custom Style and CSS

/*
  Register JavaScript that handles "Calculate Shipping" feature
  Also handles the additional form at Checkout page.
*/
function wcis_enqueue_scripts($hook) {
  // for cart and checkout only
  if(is_cart() || is_checkout() ) {
    // custom script
    wp_register_style('wcis_style', WCIS_PLUGIN_DIR . '/assets/css/style.css');
    wp_register_script('wcis_script', WCIS_PLUGIN_DIR . '/assets/js/script.js');

    wp_enqueue_script('wcis_script');
    wp_enqueue_style('wcis_style');

    // handlebars
    wp_register_script('wcis_handlebars', WCIS_PLUGIN_DIR . '/assets/js/handlebars.js');
    wp_enqueue_script('wcis_handlebars');

    // select2 from woocommerce
    $WC_DIR = str_replace(array('http:', 'https:'), '', WC()->plugin_url() );

    wp_register_script('select2', $WC_DIR . '/assets/js/select2/select2.min.js');
    wp_register_style('select2', $WC_DIR . '/assets/css/select2.css');

    wp_enqueue_script('select2');
    wp_enqueue_style('select2');
  }
}
