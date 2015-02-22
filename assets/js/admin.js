(function ( $ ) {
	'use strict';

	$( function () {

		/**
		 * Switch the options based on environment.
		 */
		$( '#woocommerce_cielo_environment' ).on( 'change', function() {
			var number = $( '#woocommerce_cielo_number' ).closest( 'tr' ),
				key = $( '#woocommerce_cielo_key' ).closest( 'tr' );

			if ( 'test' === $( this ).val() ) {
				number.hide();
				key.hide();
			} else {
				number.show();
				key.show();
			}
		}).change();

		/**
		 * Switch the options based on the store contract.
		 */
		$( '#woocommerce_cielo_store_contract' ).on( 'change', function() {
			var design = $( '#mainform h3:eq(1), #mainform .form-table:eq(1)' );

			if ( 'webservice' === $( this ).val() ) {
				design.hide();
			} else {
				design.show();
			}
		}).change();

		/**
		 * Switch the options based on the selected methods.
		 */
		$( '#woocommerce_cielo_methods' ).on( 'change', function() {
			var methods = $( this ).val(),
				debit_methods = $( '#woocommerce_cielo_debit_methods' ).closest( 'tr' ),
				authorization = $( '#woocommerce_cielo_authorization' ).closest( 'tr' ),
				debit_discount = $( '#woocommerce_cielo_debit_discount' ).closest( 'tr' );

			if ( -1 < $.inArray( 'visa', methods ) || -1 < $.inArray( 'mastercard', methods ) ) {
				debit_methods.show();
				authorization.show();
				debit_discount.show();
			} else {
				debit_methods.hide();
				authorization.hide();
				debit_discount.hide();
			}

			$( '#woocommerce_cielo_debit_methods' ).change();
		}).change();

		/**
		 * Switch the options based on the selected debit methods.
		 */
		$( '#woocommerce_cielo_debit_methods' ).on( 'change', function() {
			var debit_methods = $( this ).val(),
				debit_discount = $( '#woocommerce_cielo_debit_discount' ).closest( 'tr' ),
				methods = $( '#woocommerce_cielo_methods' ).val();

			if ( 'none' !== debit_methods && ( -1 < $.inArray( 'visa', methods ) || -1 < $.inArray( 'mastercard', methods ) ) ) {
				debit_discount.show();
			} else {
				debit_discount.hide();
			}
		}).change();
	});

}( jQuery ));
