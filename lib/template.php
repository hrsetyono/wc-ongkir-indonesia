<?php
/*
  Add Template for Select dropdown
*/

function wcis_handlebars_template() {
  ?>

  <!-- City Select -->
  <script id="wcis-city-field" type="text/x-handlebars-template">

    <select name="{{ field_id }}" id="{{ field_id }}" placeholder="Choose your City">
      <option></option>
      {{#each cities }}
        <option value="{{ city_name }}" data-id="{{ city_id }}">{{ city_name }}</option>
      {{/each }}
    </select>

  </script>


  <!-- District Wrapper -->
  <script id="wcis-district-wrap" type="text/x-handlebars-template">

    <p class="form-row form-row-wide" id="{{ id }}">
		</p>

  </script>

  <!-- District Select -->
  <script id="wcis-district-field" type="text/x-handlebars-template">

    <select name="{{ field_id }}" id="{{ field_id }}" placeholder="Choose your District">
      <option></option>
      {{#each districts }}
        <option value="{{ subdistrict_name }}" data-id="{{ subdistrict_id }}">{{ subdistrict_name }}</option>
      {{/each }}
    </select>
    <input type="hidden" name="{{ field_id }}_id" id="{{ field_id }}_id">

  </script>

  <?php
}
