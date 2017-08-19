<?php
/*
  Add Indonesian City selection to Checkout and Cart form
*/
class WCIS_Frontend {

  function __construct() {
    add_action('wp_enqueue_scripts', array($this, 'enqueue_checkout_scripts'), 1000000);
  }

  /*
    Register JS and CSS that calculates shipping and modify form
    @filter wp_enqueue_scripts

    @param $hook
  */
  function enqueue_checkout_scripts($hook) {
    if(!(is_cart() || is_checkout() ) ) { return false; }

    // custom script
    wp_enqueue_style('wcis_style', WCIS_DIR . '/assets/css/style.css');
    wp_enqueue_script('wcis_script', WCIS_DIR . '/assets/js/script.js', array('jquery'), false, true);
  }


}
