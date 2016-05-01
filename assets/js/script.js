(function($) {
'use strict';

function start() {
  Form.init();
}

/////

// TODO make it into class that accepts the jquery ID of state/city fields
var Form = function($fields) {

}

var form = {
  init: function() {
    $('#calc_shipping_country').select2();
    $('#calc_shipping_state').select2().on('change', this.stateOnChange);

    // if checkout page
    if($('.woocommerce-checkout').length) {
      this.checkoutInit();
    }
  },

  checkoutInit: function() {
    $('#billing_state').on('change', this.stateOnChange);
    var code = $('#billing_state').val();
    this.getCities(code, this._afterGetCities);
  },

  stateOnChange: function(e) {
    var code = $(this).val(),
        that = Form;
    that.getCities(code, that._afterGetCities);
  },

  // Get Cities data from the Province
  getCities: function(code, callback) {
    var data = { action: 'wcis_get_city', code: code };
    $.get(woocommerce_params.ajax_url, data, callback);
  },

  _afterGetCities: function(response) {
    var data = JSON.parse(response);

    var $cityWrapper = $('[id*="_city_field"]');
    var current_city = $cityWrapper.find('input').val();

    // create city dropdown
    var template = Handlebars.compile($('#wcis-select-city').html() );
    var html = template(data);
    $cityWrapper.html(html);

    // populate the field
    $cityWrapper.find('select').val(current_city);

    $('#calc_shipping_city').select2();
  }
};


$(document).ready(start);
$(document).on('page:load', start);

})(jQuery);
