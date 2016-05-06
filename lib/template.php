<?php
/*
  Add Template for Select dropdown
*/

function wcis_handlebars_template() {
  ?>

  <!-- Wrapper for Cart -->
  <script id="wcis-wrapper-cart" type="text/x-handlebars-template">

    <p class="form-row form-row-wide" id="{{ city.newWrapper }}">
      <select name="{{ city.newField }}" id="{{ city.newField }}" placeholder="Choose your City"></select>
    </p>

    <p class="form-row form-row-wide" id="{{ dist.newWrapper }}">
      <select name="{{ dist.newField }}" id="{{ dist.newField }}" placeholder="Choose your District"></select>
    </p>

  </script>

  <!-- Wrapper for Checkout -->
  <script id="wcis-wrapper" type="text/x-handlebars-template">

    <p class="form-row form-row-first" id="{{ city.newWrapper }}">
      <select name="{{ city.newField }}" id="{{ city.newField }}" placeholder="Choose your City"></select>
    </p>

    <p class="form-row form-row-last" id="{{ dist.newWrapper }}">
      <select name="{{ dist.newField }}" id="{{ dist.newField }}" placeholder="Choose your District"></select>
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
