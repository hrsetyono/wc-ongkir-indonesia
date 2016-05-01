<?php
/*
  Add Template for Select dropdown
*/

add_action('wp_footer', 'wcis_handlebars_template');
add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');

function wcis_handlebars_template() {
  ?>

  <!-- City Select -->
  <script id="wcis-select-city" type="text/x-handlebars-template">

    <select name="calc_shipping_city" id="calc_shipping_city">
      {{#each this }}
        <option value="{{ city_name }}">{{ city_name }}</option>
      {{/each }}
    </select>

  </script>

  <?php
}
