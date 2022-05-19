import './public.sass';
import api from './api';

const $ = jQuery;

const checkoutFields = {
  init() {
    // fix the weird WooCommerce interaction where it initially trigger 'change' on State field
    this.isFirstRun = true;

    const $body = document.querySelector('body');

    // abort if not in Checkout page
    if (!$body.classList.contains('woocommerce-checkout')) { return; }

    // TODO: Change this to pure JS
    $(document).on('change', '#billing_country, #shipping_country', this.toggleCityField.bind(this));
    $(document).on('change', '#billing_state, #shipping_state', this.populateCitiesDropdown.bind(this));

    // Create the City and District dropdown
    this.createFields();
    $(document).on('change', '#_billing_city, #_shipping_city', this.populateDistrictsDropdown.bind(this));
    $(document).on('change', '#_billing_district, #_shipping_district', this.fillCityField.bind(this));
  },

  /**
   * Inititate both fields
   */
  createFields() {
    const $cityFields = document.querySelectorAll('#billing_city, #shipping_city');

    $cityFields.forEach(async ($f) => {
      const type = $f.getAttribute('id') === 'billing_city' ? 'billing' : 'shipping';
      const $outerWrapper = $f.closest('.woocommerce-billing-fields, .woocommerce-shipping-fields');
      const $wrapper = $f.closest('#billing_city_field, #shipping_city_field');

      // get province code
      let provCode = $outerWrapper.querySelector('#billing_state, #shipping_state').value;
      provCode = provCode || '0';

      // get district ID
      let districtID = $f.value.match(/\[(\d+)\]/);
      districtID = districtID ? districtID[1] : '0';

      // get custom fields and append it after the City field
      const result = await api.get(`/fields/${type}/${provCode}/${districtID}`);
      $($wrapper).after(result);

      // hide the custom field if country not ID
      $('#billing_country, #shipping_country').trigger('change');
    });
  },

  /**
   * Show or Hide the original City field depending on the Country selected
   */
  toggleCityField(e) {
    const $wrapper = e.currentTarget.closest('.woocommerce-billing-fields, .woocommerce-shipping-fields');
    const $ogCityField = $wrapper.querySelector('#billing_city_field, #shipping_city_field');

    // the custom dropdown
    const $citiesField = $wrapper.querySelector('#_billing_city_field, #_shipping_city_field');
    const $districtsField = $wrapper.querySelector('#_billing_district_field, #_shipping_district_field');

    // If country is ID, hide the original City field
    if (e.currentTarget.value === 'ID') {
      $ogCityField.style.display = 'none';

      if ($citiesField) {
        $citiesField.style.display = 'block';
      }

      if ($districtsField) {
        $districtsField.style.display = 'block';
      }
    } else { // Else, hide the dropdown and show the original field
      $ogCityField.style.display = 'block';

      if ($citiesField) {
        $citiesField.style.display = 'none';
      }

      if ($districtsField) {
        $districtsField.style.display = 'none';
      }
    }
  },

  /**
   * Populate the City dropdown according to selected State
   *
   * TODO: change to pure JS
   */
  async populateCitiesDropdown(e) {
    const provCode = $(e.currentTarget).val() || '0';
    const $wrapper = $(e.currentTarget).closest('.woocommerce-billing-fields, .woocommerce-shipping-fields');

    // if not first run, empty out the city field
    if (!this.isFirstRun) {
      const $ogCityField = $wrapper.find('#billing_city_field, #shipping_city_field');
      $ogCityField.find('input').val('');
    }

    // add 'Loading' message to existing field
    const $citiesSelect = $wrapper.find('#_billing_city_field select, #_shipping_city_field select');
    $citiesSelect.html('<option>Loading...</option>');

    // Add notice message to districts field
    const $districtsSelect = $wrapper.find('#_billing_district_field select, #_shipping_district_field select');
    $districtsSelect.html('<option>Pilih Kota terlebih dahulu...</option>');

    const result = await api.get(`/cities/${provCode}`);

    let options = '';
    Object.keys(result).forEach((id) => {
      options += `<option value="${id}">${result[id]}</option>`;
    });

    $citiesSelect.html(options);
    this.isFirstRun = false;
  },

  /**
   * Populate the District dropdown according to selected City
   *
   * TODO: Change to pure JS
   */
  async populateDistrictsDropdown(e) {
    const cityID = $(e.currentTarget).val();
    const $wrapper = $(e.currentTarget).closest('.woocommerce-billing-fields, .woocommerce-shipping-fields');
    const provCode = $wrapper.find('#billing_state, #shipping_state').val();

    // add 'Loading' message to district field
    const $districtsSelect = $wrapper.find('#_billing_district_field select, #_shipping_district_field select');
    $districtsSelect.html('<option>Loading...</option>');

    const result = await api.get(`/districts/${provCode}/${cityID}`);

    let options = '';
    Object.keys(result).forEach((id) => {
      options += `<option value="${id}">${result[id]}</option>`;
    });

    $districtsSelect.html(options);
  },

  /**
   * Fill the hidden City field after selecting the City and District dropdown
   *
   * TODO: change to pure JS
   */
  fillCityField(e) {
    const $select = $(e.currentTarget);
    const $wrapper = $select.closest('.woocommerce-billing-fields, .woocommerce-shipping-fields');
    const $cityInput = $wrapper.find('#billing_city, #shipping_city');

    $cityInput.val($select.val());

    // trigger delivery cost refresh
    $cityInput.trigger('keydown');
  },
};

function onReady() {
  checkoutFields.init();
}

function onLoad() {
  // script that runs when everything is loaded
}

document.addEventListener('DOMContentLoaded', onReady);
window.addEventListener('load', onLoad);
