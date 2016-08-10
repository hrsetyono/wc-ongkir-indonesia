(function($) {
'use strict';

function start() {
  // if cart page and has item
  if($('.woocommerce-cart').length && $('.shop_table').length) {
    var calcField = new Fields('calc_shipping');
    calcField.init();
  }

  // if checkout page but not Order Received page
  else if($('.woocommerce-checkout:not(.woocommerce-order-received)').length) {
    var billingField = new Fields('billing');
    billingField.init();

    var shippingField = new Fields('shipping');
    shippingField.init();
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

  this.country = {
    field: type + '_country',
    wrapper: type + '_country_field'
  };

  this.state = {
    field: type + '_state',
    wrapper: type + '_state_field',
    changeCounter: 0 // WooCommerce has bug where State Field trigger its "change" event twice
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

  this.destination = type + '_destination_id';

  // parse the city value, the format is "City, District [code]"
  var cityRaw = $('#' + this.city.field).val();

  var cityValue = cityRaw.match(/[\w\s]+/);
  this.city.value = cityValue ? cityValue[0] : null;

  var distValue = cityRaw.match(/,\s([\w\s]+)\s*/)
  this.dist.value = distValue ? distValue[1].trim() : null;
};

Fields.prototype = {
  init: function() {
    var self = this;

    // add wrapper for the new fields
    var args = { city: self.city, dist: self.dist };

    var templateId = (self._isCartPage() ) ? '#wcis-wrapper-cart' : '#wcis-wrapper';

    var template = Handlebars.compile($(templateId).html() );
    var html = template(args);

    // append template and hide the real city field
    $('#' + self.city.wrapper).append(html).addClass('hide');
    $('#' + self.city.field).hide();

    // initiate the event handler
    this.initCountry();
    this.initState();
    this.initCity();
    this.initDistrict();
  },

  initCountry: function() {
    var self = this;

    // if cart page, hide the country
    if(self._isCartPage() ) {
      $('#' + self.country.wrapper).hide();
    }
  },

  /*
    Add Listener to Province / State field
  */
  initState: function() {
    var self = this;
    var $field = $('#' + self.state.field);

    if(self._isCartPage() ) {
      // $field.select2();
    }

    $field.off('change');
    $field.on('change', _onChange);

    function _onChange(e) {
      $('#' + self.city.field).val('').change();

      // prevent the first change to trigger (bug from WC where it triggers twice)
      // TODO: if bug resolved, remove this
      if(self._isCartPage() || self.state.changeCounter) {
        $('#' + self.city.newField).trigger('wcis-state-selected');
      }

      self.state.changeCounter += 1;
    }
  },

  /*
    Create a City Selection based on Province
  */
  initCity: function() {
    var self = this;
    var $field = $('#' + self.city.newField);
    var $wrapper = $('#' + self.city.wrapper);

    // hide city first
    // $wrapper.addClass('hide');

    $field.off('wcis-state-selected');
    $field.on('wcis-state-selected', _onStateSelected);

    $field.off('change');
    $field.on('change', _onChange);

    function _onStateSelected(e) {
      // console.log('state selected');
      // remove all options first
      $(this).empty();

      // also remove district
      $('#' + self.dist.newWrapper).hide();
      $('#' + self.dist.newField).empty();

      api.getCities($('#' + self.state.field), _onGetCities);
    }

    function _onGetCities(response) {
      // console.log('get city');
      var args = JSON.parse(response);

      // insert template
      var template = Handlebars.compile($('#wcis-city-option').html() );
      var html = template(args);

      $field.append(html); //.select2();

      // show the city field
      $wrapper.removeClass('hide');

      // prepopulate
      if(self.city.value) {
        var optionTarget = 'option:contains("' + self.city.value + '")';
        $field.find(optionTarget).prop('selected', true).trigger('change');
      }
    }

    function _onChange(e) {
      $('#' + self.dist.newField).trigger('wcis-city-selected');

      // empty out the city field and store it in variable
      $('#' + self.city.field).val('');
      self.city.value = $(this).find('option:selected').text();
    }
  },

  /*
    Create a District Selection based on City
  */
  initDistrict: function() {
    var self = this;
    var $field = $('#' + self.dist.newField);
    var $wrapper = $('#' + self.dist.newWrapper);

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
      var args = JSON.parse(response);

      // insert template
      var template = Handlebars.compile($('#wcis-dist-option').html() );
      var html = template(args);

      $field.append(html); //.select2();

      // show district
      $wrapper.show();

      // prepopulate
      if(self.dist.value) {
        var optionTarget = 'option:contains("' + self.dist.value + '")';
        $field.find(optionTarget).prop('selected', true).trigger('change');
      }
    }

    function _onChange(e) {
      // create the string for real city field
      self.dist.value = $(this).find('option:selected').text();
      var destinationId = ' [' + $(this).val() + ']';

      // TODO remove this after debug
      // var cityId = ' (' + $('#' + self.city.newField).val() + ')';
      // $('#' + self.city.field).val(self.city.value + cityId + ', ' + self.dist.value + destinationId);

      $('#' + self.city.field).val(self.city.value + ', ' + self.dist.value + destinationId);
    }
  },

  /////

  _isCartPage: function() {
    return this.type === 'calc_shipping';
  },

  _isCheckoutPage: function() {
    return this.type === 'shipping' || this.type === 'billing';
  }
};

$(document).ready(start);
$(document).on('page:load', start);

})(jQuery);
