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
    checkout.init();
  }
}

/////


// ----- API Module -----
var api = {
  // Get all cities in that state
  getCities: function($state, callback) {
    var code = $state.val();

    if(code) {
      $.get(woocommerce_params.ajax_url,
        { action: 'wcis_get_cities', code: code },
        callback );
    }
  },

  // Get all districts in that state
  getDistricts: function($city, callback) {
    var id = $city.val();

    if(id) {
      $.get(woocommerce_params.ajax_url,
        { action: 'wcis_get_districts', id: id },
        callback );
    }
  },
};

/*
  Handle City and District fields

  @param string type - Either calc, billing, or shipping
  @param obj fields - List of class and ID of all targets
*/
function Fields(type, args) {
  this.type = type;

  this.state = {
    field: type + '_state',
    wrapper: type + '_state_field'
  };

  this.city = {
    field: type + '_city',
    wrapper: type + '_city_field',
    newField: type + '_wcis_c',
    newWrapper: type + '_wcis_c_wrapper'
  };

  this.dist = {
    newField: type + '_wcis_d',
    newWrapper: type + '_wcis_d_wrapper'
  };

  // parse the city value, the format is "City, District"
  var cityRaw = $('#' + this.city.field).val();
  this.city.value = cityRaw.split(', ')[0];
  this.dist.value = cityRaw.split(', ')[1];
};

Fields.prototype = {
  init: function() {
    var self = this;

    // add wrapper for the new fields
    var args = { city: self.city, dist: self.dist };

    var template = Handlebars.compile($('#wcis-wrapper').html() );
    var html = template(args);

    // append template and hide the real city field
    $('#' + self.city.wrapper).append(html);
    // $('#' + this.city.field).hide();

    // initiate the event handler
    this.initState();
    this.initCity();
    this.initDistrict();
  },

  /*
    Add Listener to Province / State field
  */
  initState: function() {
    var self = this;
    var $field = $('#' + self.state.field);

    $field.off('change');
    $field.on('change', function(e) {
      console.log('state changed');
      $('#' + self.city.newField).trigger('wcis-state-selected');
    });
  },

  /*
    Create a City Selection based on Province
  */
  initCity: function() {
    var self = this;
    var $field = $('#' + self.city.newField);

    $field.off('wcis-state-selected');
    $field.on('wcis-state-selected', _onStateSelected);

    $field.off('change');
    $field.on('change', _onChange);

    function _onStateSelected(e) {
      // remove all options first
      $(this).empty();

      // also remove district
      $(self.dist.newWrapper).hide();
      $(self.dist.newField).empty();

      api.getCities($('#' + self.state.field), _onGetCities);
    }

    function _onGetCities(response) {
      console.log('get city');
      var args = JSON.parse(response);

      // insert template
      var template = Handlebars.compile($('#wcis-city-option').html() );
      var html = template(args);

      $field.append(html);

      if(self.city.value) {
        var optionTarget = 'option:contains("' + self.city.value + '")';
        $field.find(optionTarget).prop('selected', true).trigger('change');
      }
    }

    function _onChange(e) {
      $('#' + self.dist.newField).trigger('wcis-city-selected');

      // add the string for real city field
      self.city.value = $(this).find('option:selected').text();
      $('#' + self.city.field).val(self.city.value);
    }
  },

  /*
    Create a District Selection based on City
  */
  initDistrict: function() {
    var self = this;
    var $field = $('#' + self.dist.newField);

    $field.off('wcis-city-selected');
    $field.on('wcis-city-selected', _onCitySelected);

    $field.off('change');
    $field.on('change', _onChange);

    function _onCitySelected(e) {
      // remove all options first
      $(this).empty();

      api.getDistricts($('#' + self.city.newField), _onGetDistricts);
    }

    function _onGetDistricts(response) {
      console.log('get district');
      var args = JSON.parse(response);

      // insert template
      var template = Handlebars.compile($('#wcis-dist-option').html() );
      var html = template(args);

      $field.append(html);
    }

    function _onChange(e) {
      // create the string for real city field
      self.dist.value = $(this).find('option:selected').text();
      $('#' + self.city.field).val(self.city.value + ', ' + self.dist.value);
    }

  }
};

var checkout = {
  init: function() {
    var billingField = new Fields('billing');
    billingField.init();
  },
};

$(document).ready(start);
$(document).on('page:load', start);

})(jQuery);
