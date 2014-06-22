(function ( $ ) {
	'use strict';

	$( function () {

		/**
		 * Switch the options based on environment.
		 *
		 * @param {string} type
		 */
		function switchEnvironment( type ) {
			var fields = $( '#mainform .form-table:eq(0) tr:eq(4), #mainform .form-table:eq(0) tr:eq(5)' );

			if ( 'test' === type ) {
				fields.hide();
			} else {
				fields.show();
			}
		}

		switchEnvironment( $( '#woocommerce_cielo_environment' ).val() );

		$( '#woocommerce_cielo_environment' ).on( 'change', function () {
			switchEnvironment( $( this ).val() );
		});

		/**
		 * Switch the options based on the selected methods/
		 *
		 * @param {array} methods
		 */
		function switchMethods( methods ) {
			var fields = $( '#mainform .form-table:eq(0) tr:eq(7), #mainform .form-table:eq(0) tr:eq(10)' );

			if ( -1 < $.inArray( 'visa', methods ) || -1 < $.inArray( 'mastercard', methods ) ) {
				fields.show();
			} else {
				fields.hide();
			}
		}

		switchMethods( $( '#woocommerce_cielo_methods' ).val() );

		$( '#woocommerce_cielo_methods' ).on( 'change', function () {
			switchMethods( $( this ).val() );
		});
	});

}( jQuery ));
