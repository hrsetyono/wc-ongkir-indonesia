<?php

class WCIS_Frontend {

  function __construct() {
    add_action('wp_enqueue_scripts', array($this, 'enqueue_checkout_scripts'), 999);
    add_action('wp_footer', array($this, 'footer_checkout_template') );
  }

  /*
    Register JavaScript that handles "Calculate Shipping" feature
    Also handles the additional form at Checkout page.
  */
  function enqueue_checkout_scripts($hook) {
    if(!(is_cart() || is_checkout() ) ) {
      return false;
    }

    // select2 from woocommerce
    $WC_DIR = str_replace(array('http:', 'https:'), '', WC()->plugin_url() );

    wp_register_script('select2', $WC_DIR . '/assets/js/select2/select2.min.js');
    wp_register_style('select2', $WC_DIR . '/assets/css/select2.css');

    wp_enqueue_script('select2');
    wp_enqueue_style('select2');

    // custom script
    wp_register_style('wcis_style', WCIS_DIR . '/assets/css/style.css');
    wp_register_script('wcis_script', WCIS_DIR . '/assets/js/script.js');

    wp_enqueue_script('wcis_script');
    wp_enqueue_style('wcis_style');

    // handlebars
    wp_register_script('handlebars', WCIS_DIR . '/assets/js/handlebars.js');
    wp_enqueue_script('handlebars');
  }

  /*
    Add Template for Select dropdown
  */

  function footer_checkout_template() {
    if(!(is_cart() || is_checkout() ) ) {
      return false;
    }
    ?>

    <!-- Wrapper for Cart -->
    <script id="wcis-wrapper-cart" type="text/x-handlebars-template">

      <p class="form-row form-row-wide" id="{{ city.newWrapper }}">
        <select name="{{ city.newField }}" id="{{ city.newField }}" placeholder="Choose City"></select>
      </p>

      <p class="form-row form-row-wide" id="{{ dist.newWrapper }}">
        <select name="{{ dist.newField }}" id="{{ dist.newField }}" placeholder="Choose District"></select>
      </p>

    </script>

    <!-- Wrapper for Checkout -->
    <script id="wcis-wrapper" type="text/x-handlebars-template">

      <p class="form-row form-row-first" id="{{ city.newWrapper }}">
        <select name="{{ city.newField }}" id="{{ city.newField }}" placeholder="Choose City"></select>
      </p>

      <p class="form-row form-row-last" id="{{ dist.newWrapper }}">
        <select name="{{ dist.newField }}" id="{{ dist.newField }}" placeholder="Choose District">
        </select>
      </p>

    </script>

    <!-- City Select -->
    <script id="wcis-city-option" type="text/x-handlebars-template">

      <option></option>
      {{#each this }}
        <option value="{{ city_id }}">{{ city_name }}</option>
      {{/each }}

    </script>

    <!-- District Select -->
    <script id="wcis-dist-option" type="text/x-handlebars-template">

      <option></option>
      {{#each this }}
        <option value="{{ subdistrict_id }}">{{ subdistrict_name }}</option>
      {{/each }}

    </script>

    <script>
      var wcis_post = <?php echo json_encode($_POST); ?>;
    </script>

    <?php
  }

}
