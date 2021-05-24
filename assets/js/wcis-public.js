import '../sass/wcis-public.sass';

(function($) { 'use strict';

document.addEventListener( 'DOMContentLoaded', onReady );
window.addEventListener( 'load', onLoad );

function onReady() {
  checkoutFields.init();
}

function onLoad() {
  // script that runs when everything is loaded
}

let checkoutFields = {
  init() {
    this.isFirstRun = true; // fix the weird WooCommerce interaction where it initially trigger 'change' on State field

    let $body = document.querySelector( 'body' );

    // abort if not in Checkout page
    if( !$body.classList.contains( 'woocommerce-checkout' ) ) { return; }

    // Create the City and District dropdown
    this.createFields();

    // TODO: Change this to pure JS
    $( document ).on( 'change', '#billing_country, #shipping_country', this.toggleCityField.bind( this ) );
    $( document ).on( 'change', '#billing_state, #shipping_state', this.populateCitiesDropdown.bind( this ) );
    $( document ).on( 'change', '#_billing_city, #_shipping_city', this.populateDistrictsDropdown.bind( this ) );
    $( document ).on( 'change', '#_billing_district, #_shipping_district', this.fillCityField.bind( this ) );

    $( '#billing_country, #shipping_country' ).trigger( 'change' );
  },

  /**
   * Inititate both fields
   */
  createFields() {
    let $cityFields = document.querySelectorAll( '#billing_city, #shipping_city' );

    for( let $f of $cityFields ) {
      let type = $f.getAttribute( 'id' ) == 'billing_city' ? 'billing' : 'shipping';
      let $outerWrapper = $f.closest( '.woocommerce-billing-fields, .woocommerce-shipping-fields' );
      let $wrapper = $f.closest( '#billing_city_field, #shipping_city_field' );

      // get province code
      let provCode = $outerWrapper.querySelector( '#billing_state, #shipping_state' ).value;
      provCode = provCode || '0';

      // get district ID
      let districtID = $f.value.match( /\[(\d+)\]/ );
      districtID = districtID ? districtID[1] : '0';

      // get custom fields and append it afterthe City field
      wcisAPI.get( wcisLocalize.WCIS_API + `/fields/${type}/${provCode}/${districtID}` )
        .then( result => {
          $( $wrapper ).after( result );
        });
    }
  },

  /**
   * Show or Hide the original City field depending on the Country selected 
   */
  toggleCityField( e ) {
    let $wrapper = e.currentTarget.closest( '.woocommerce-billing-fields, .woocommerce-shipping-fields' );
    let $ogCityField = $wrapper.querySelector( '#billing_city_field, #shipping_city_field' );

    // the custom dropdown
    let $citiesField = $wrapper.querySelector( '#_billing_city_field, #_shipping_city_field' );
    let $districtsField = $wrapper.querySelector( '#_billing_district_field, #_shipping_district_field' );

    // If country is ID, hide the original City field
    if( e.currentTarget.value == 'ID' ) { 
      $ogCityField.classList.add( 'wcis-form-row-hidden' );

      $citiesField && $citiesField.classList.remove( 'wcis-form-row-hidden' );
      $districtsField && $districtsField.classList.remove( 'wcis-form-row-hidden' );
    }
    // Else, hide the dropdown and show the original field
    else {
      $ogCityField.classList.remove( 'wcis-form-row-hidden' );

      $citiesField && $citiesField.classList.add( 'wcis-form-row-hidden' );
      $districtsField && $districtsField.classList.add( 'wcis-form-row-hidden' );
    }
  },

  /**
   * Populate the City dropdown according to selected State
   * 
   * TODO: change to pure JS
   */
   populateCitiesDropdown( e ) {
    let provCode = $( e.currentTarget ).val() || '0';
    let $wrapper = $( e.currentTarget ).closest( '.woocommerce-billing-fields, .woocommerce-shipping-fields' );

    // if not first run, empty out the city field
    if( !this.isFirstRun ) {
      let $ogCityField = $wrapper.find( '#billing_city_field, #shipping_city_field' );
      $ogCityField.find( 'input' ).val( '' );
    }

    // add 'Loading' message to existing field
    let $citiesSelect = $wrapper.find( '#_billing_city_field select, #_shipping_city_field select' );  
    $citiesSelect.html( '<option>Loading...</option>' );

    // Add notice message to districts field
    let $districtsSelect = $wrapper.find( '#_billing_district_field select, #_shipping_district_field select' );  
    $districtsSelect.html( '<option>Pilih Kota terlebih dahulu...</option>' );


    wcisAPI.get( wcisLocalize.WCIS_API + '/cities/' + provCode )
      .then( result => {
        let options = '';
        for( let id in result ) {
          options += `<option value="${id}">${result[id]}</option>`;
        }

        $citiesSelect.html( options );
      });

    this.isFirstRun = false;
  },

  /**
   * Populate the District dropdown according to selected City
   * 
   * TODO: Change to pure JS
   */
   populateDistrictsDropdown( e ) {
    let cityID = $( e.currentTarget ).val();
    let $wrapper = $( e.currentTarget ).closest( '.woocommerce-billing-fields, .woocommerce-shipping-fields' );
    let provCode = $wrapper.find( '#billing_state, #shipping_state' ).val();

    // add 'Loading' message to district field
    let $districtsSelect = $wrapper.find( '#_billing_district_field select, #_shipping_district_field select' );
    $districtsSelect.html( '<option>Loading...</option>' );

    wcisAPI.get( wcisLocalize.WCIS_API + '/districts/' + provCode + '/' + cityID )
      .then( result => {
        let options = '';
        for( let id in result ) {
          options += `<option value="${id}">${result[id]}</option>`;
        }

        $districtsSelect.html( options );
      });
  },


  /**
   * Fill the hidden City field after selecting the City and District dropdown
   * 
   * TODO: change to pure JS
   */
  fillCityField( e ) {
    let $select = $( e.currentTarget );
    let $wrapper = $select.closest( '.woocommerce-billing-fields, .woocommerce-shipping-fields' );
    let $cityInput = $wrapper.find( '#billing_city, #shipping_city' );

    $cityInput.val( $select.val() );

    // trigger delivery cost refresh
    $cityInput.trigger( 'keydown' );
  },
};



/**
 * Simple GET and POST functions that return Promise.
 * 
 * Example:
 *   wcisAPI.get( url ).then( result => { .. } );
 *   wcisAPI.post( url, data ).then( result => { ... } );
 */
let wcisAPI = {
  get( endpoint ) {
    return window.fetch( endpoint, {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    } )
    .then( this._handleError )
    .then( this._handleContentType )
    .catch( this._throwError );
  },

  post( endpoint, body ) {
    return window.fetch( endpoint, {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify( body ),
    } )
    .then( this._handleError )
    .then( this._handleContentType )
    .catch( this._throwError );
  },

  _handleError( err ) {
    return err.ok ? err : Promise.reject( err.statusText )
  },

  _handleContentType( res ) {
    const contentType = res.headers.get( 'content-type' );
    if( contentType && contentType.includes( 'application/json' ) ) {
      return res.json()
    }
    return Promise.reject( 'Oops, we haven\'t got JSON!' )
  },

  _throwError( err ) {
    throw new Error( err );
  }
};

})(jQuery);
