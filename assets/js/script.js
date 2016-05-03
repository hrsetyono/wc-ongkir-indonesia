(function($) {
'use strict';

function start() {
  // if cart page
  if($('.woocommerce-cart').length) {
    var args = {
      country: '#calc_shipping_country',
      state: '#calc_shipping_state',
      city: '#calc_shipping_city',
      cityWrapper: '#calc_shipping_city_field',
      district: '#shipping_district',
      districtWrapper: '#shipping_district_field'
    };
    var form = new Form('cart', args);
    form.init();
  }

  // if checkout page
  else if($('.woocommerce-checkout').length) {
    var args = {
      state: '#billing_state',
      city: '#billing_city',
      cityWrapper: '#billing_city_field'
    };

    var form = new Form('checkout', args);
    form.init();
  }
}

/////

// ----- FORM CLASS -----

function Form(type, args) {
  this.type = type;
  this.args = args;

  this.data = {};
}

Form.prototype = {
  /*
    Init the Form fields handler
  */
  init: function() {
    var self = this;

    switch(self.type) {
      // CART
      case 'cart':

        // hide country
        $(self.args.country).hide();

        $(self.args.state).select2().on('change', _onStateChange);

        // after selecting shipping method
        $(document.body).on('updated_shipping_method', _onSelectMethod.bind(self) );

        // create district field
        var args = { id: self.args.districtWrapper.replace('#', '') };
        var template = Handlebars.compile($('#wcis-district-wrap').html() );
        var html = template(args);
        $(self.args.cityWrapper).after(html);

        break;

      // CHECKOUT
      case 'checkout':
        $(self.args.state).on('change', _onStateChange);
        break;
    }

    /////

    function _onStateChange(e) {
      var code = $(this).val();

      // if not empty get the API, if empty hide the city
      if(code) {
        self.getCities(code);
      } else {
        $(self.args.cityWrapper).hide();
      }
    }

    function _onSelectMethod(e) {
      // remove previous listener
      $(self.args.state).off('change');
      $(document.body).off('updated_shipping_method');

      // reinitiate and trigger change
      self.init();
      $(self.args.state).trigger('change');
    }
  },

  /*
    Get Cities data from the Province
  */
  getCities: function(code) {
    var self = this;

    var data = { action: 'wcis_get_cities', code: code };
    $.get(woocommerce_params.ajax_url, data, _afterGetCities);

    /////

    /*
      Make CITY input field into <select> and insert the data we have received.
    */
    function _afterGetCities(response) {
      var args = {
        cities: JSON.parse(response),
        field_id: self.args.city.replace('#', '')
      };

      var currentVal = $(self.args.city).val();

      // create city dropdown
      var template = Handlebars.compile($('#wcis-city-field').html() );
      var html = template(args);

      var $cityWrapper = $(self.args.cityWrapper);
      $cityWrapper.html(html);

      // populate the field
      var $select = $cityWrapper.find('select');
      $select.val(currentVal);

      $select.select2({
        placeholder: $select.attr('placeholder')
      });

      $select.on('change', _onCityChange);

      $cityWrapper.show();
    }

    function _onCityChange(e) {
      var cityId = $(this).find(':selected').data('id');

      var data = { action: 'wcis_get_districts', city: cityId };
      $.get(woocommerce_params.ajax_url, data, _afterGetDistricts);
    }

    function _afterGetDistricts(response) {
      var $distWrapper = $(self.args.districtWrapper);
      $distWrapper.empty(); // empty out previous content

      var args = {
        field_id: self.args.district.replace('#', ''),
        districts: JSON.parse(response)
      };

      var template = Handlebars.compile($('#wcis-district-field').html() );
      var html = template(args);

      $distWrapper.html(html);

      // convert to select2 and add listener
      var $select = $(self.args.district);
      $select.select2({
        placeholder: $select.attr('placeholder')
      }).on('change', _onDistrictChange);
    }

    function _onDistrictChange(e) {
      var districtId = $(this).find(':selected').data('id');
      $('#shipping_district_id').val(districtId);
    }
  },
};

var form = {

  checkoutInit: function() {
    $('#billing_state').on('change', this.stateOnChange);
    var code = $('#billing_state').val();
    this.getCities(code, this._afterGetCities);
  },
};


$(document).ready(start);
$(document).on('page:load', start);

})(jQuery);
