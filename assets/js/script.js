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
    startCheckout.init();
  }
}

// Starter for Checkout functionality
var startCheckout = {
  init: function() {
    var self = this;

    // dropdown listener
    $(document).on('country_to_state_changed', self.onCountryChanged.bind(self) );
    $('.woocommerce').on('change', '.state_select', self.onStateChanged);
    $('.woocommerce').on('change', '[name*="_city_select"]', self.onCityChanged);

    self.createCitySelect();
  },

  /*
    Hide and remove City SELECT when country is changed to not Indonesia

    @param country (string) - Country code
    @param $wrapper (DOM) - The outer div of Billing or Shipping fieldset
  */
  onCountryChanged: function(e, country, $wrapper) {
    var self = this;
    var $field = $wrapper.find('[id*="_city_field"]');

    if(country === 'ID') {
      $field.addClass('form-row--select');
    } else {
      $field.removeClass('form-row--select');
      $field.find('select').html('').change();
    }
  },

  /*
    Listener when State dropdown is changed
  */
  onStateChanged: function(e) {
    var $state = $(this);
    var $wrapper = $state.closest('.woocommerce-billing-fields, .woocommerce-shipping-fields');
    var $cityField = $wrapper.find('[id*="_city_field"]');
    var $country = $wrapper.find('[name="billing_country"], [name="shipping_country"]');

    // only run if Country is Indonesia
    if($country.val() === 'ID') {
      var cityField = new CityField($state.val(), $cityField);
      $('.woocommerce').off('change.citySelect');
      cityField.init();
    }
  },

  /*
    After selecting city dropdown, copy the value to the Input text
  */
  onCityChanged: function(e) {
    var $select = $(this);

    $select.closest('.form-row')
      .find('input').val($select.val() )
      .change();
  },

  /*
    Create empty Select, add "_disabled" in the name so it become hidden
  */
  createCitySelect: function() {
    var $wrapper = $('.woocommerce-billing-fields, .woocommerce-shipping-fields');

    $wrapper.each(function() {
      var $cityField = $(this).find('[id*="_city_field"]');

      // get the name and append "_disabled"
      var name = $cityField.find('input').attr('name') + '_select';
      var selectHtml = '<select name="' + name + '"></select>';

      $cityField.append(selectHtml);
      $cityField.find('select').select2({
        templateSelection: myTemplateSelection,
        matcher: myMatcher,
      });
    });

    /////

    function myTemplateSelection(opt) {
      if (!opt.id) { return opt.text; } // optgroup
      var city = $(opt.element).data('city');

      return city + ' – ' + opt.text;
    }


    function myMatcher(params, data) {
      var term = $.trim(params.term).toUpperCase(); // the search term
      var text = $.trim(data.text).toUpperCase(); // the option text

      // If there are no search terms, return all of the data
      if (term === '') { return data; }

      // if <optgroup>
      if (data.children && data.children.length > 0) {

        var match = $.extend(true, {}, data); // clone data

        // if Group title matches, return all children
        if(text.indexOf(term) > -1) {
          return match;
        }

        // else loop through children
        for (var c = data.children.length - 1; c >= 0; c--) {
          var child = data.children[c];
          var matches = myMatcher(params, child);

          // If there wasn't a match, remove the child in array
          if (matches == null) { match.children.splice(c, 1); }
        }

        // if any children matches, return the group
        if (match.children.length > 0) {
          return match;
        }

        // If there were no matching children, check just the plain object
        return myMatcher(params, match);
      }

      // Check if the text contains the term
      if (text.indexOf(term) > -1) {
        return data;
      }

      // If it doesn't contain the term, don't return anything
      return null;
    }
  },

};


/*
  Handle City field

  @param state (string) - The selected state
  @param $field (DOM) - City field wrapper
*/
function CityField(state, $field) {
  var self = this;
  self.state = state;
  self.$field = $field;
  self.$input = self.$field.find('input');
  self.inputVal = self.$input.val();
  self.$select = self.$field.find('select');

  self.$input.val(''); // empty out field
}

CityField.prototype = {
  init: function() {
    var self = this;

    // populate dropdown initially
    self.getData(self.fillSelect.bind(self) );
  },


  /*
    Get cities data

    @param callback (func) - Function to run after AJAX success
  */
  getData: function(callback) {
    var self = this;

    if(self.state) {
      $.get(woocommerce_params.ajax_url, { action: 'wcis_get_cities', state: self.state }, callback);
    }
  },

  /*
    Fill City dropdown with data

    @param data (obj) - Response data of cities
  */
  fillSelect: function(data) {
    var self = this;
    data = $.map(JSON.parse(data), function(el) { return el }); // parse data

    var selectHtml = '';

    // get current district to preselect the City dropdown
    var districtRegex = /,\s([\w\d\s ]+)/g.exec(self.inputVal);
    var currentDistrict = districtRegex ? districtRegex[1].trim() : '';

    // loop cities
    for(var cityId in data) {
      var c = data[cityId];
      selectHtml += '<optgroup label="' + c.city_name + '">';

      // loop districts
      for(var distId in c.districts) {
        var d = c.districts[distId];
        var value = ' value="' + c.city_name + ', ' + d + ' [' + distId + ']"';
        var dataCity = ' data-city="' + c.city_name + '" ';
        var isSelected = '';

        // if same as current district, preselect it
        if(currentDistrict === d) {
          isSelected = ' selected="selected" ';
        }

        selectHtml += '<option' + dataCity + value + isSelected + '>' + d + '</option>';
      }

      selectHtml += '</optgroup>';
    }

    // if district not found, add a placeholder option at top of Select
    if(!isSelected) {
      var placeholder = '<option value="" selected="selected" disabled>Please select your City</option>';
      selectHtml = placeholder + selectHtml;
    }

    self.$select.html(selectHtml).change();
    self.$field.addClass('form-row--select');
  },

}



$(document).ready(start);
$(document).on('page:load', start);

})(jQuery);
