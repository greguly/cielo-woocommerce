( function( $ ) {
	'use strict';

	$( function() {

		/**
		 * Switch the options based on the store contract.
		 */
		$( '[id^="woocommerce_cielo"][id$="store_contract"]' ).on( 'change', function() {
			var design      = $( '[id^="woocommerce_cielo"][id$="_design"]' ).closest( 'tr' ),
				designTitle = design.closest( 'table' ).prev( 'h3' );

			if ( 'webservice' === $( this ).val() ) {
				design.hide();
				designTitle.hide();
			} else {
				design.show();
				designTitle.show();
			}
		}).change();

		/**
		 * Switch the options based on Enable Sale Capture.
		 */
		$( '#woocommerce_cielo_general_settings_api_version' ).on( 'change', function() {
            var admin_sale_capture = $( '#woocommerce_cielo_general_settings_admin_sale_capture' ).closest( 'tr' ),
				time_sale_capture = $( '#woocommerce_cielo_general_settings_time_sale_capture' ).closest( 'tr' ),
				cielo_direct_debit = $( 'a[href$="cielo_direct_debit"]' ).closest( 'ul > li' ),
				cielo_banking_ticket = $( 'a[href$="cielo_banking_ticket"]' ).closest( 'ul > li' );

			if ( 'version_1_5' === $( this ).val() ) {
				admin_sale_capture.hide();
				time_sale_capture.hide();

                cielo_direct_debit.hide();
                cielo_banking_ticket.hide();
			} else {
				admin_sale_capture.show();
				if ( admin_sale_capture.is(":checked") ) {
					time_sale_capture.show();
				}

                cielo_direct_debit.show();
                cielo_banking_ticket.show();
			}
		}).change();

		/**
		 * Switch the options based on Enable Sale Capture.
		 */
		$( '#woocommerce_cielo_general_settings_admin_sale_capture' ).on( 'change', function() {
            var time_sale_capture = $( '#woocommerce_cielo_general_settings_time_sale_capture' ).closest( 'tr' );

			if ( $( this ).is(":checked") ) {
				time_sale_capture.show();
			} else {
				time_sale_capture.hide();
			}
		}).change();

		/**
		 * Switch the options based on installment type.
		 */
		$( '#woocommerce_cielo_credit_installment_type' ).on( 'change', function() {
			var interest_rate = $( '#woocommerce_cielo_credit_interest_rate' ).closest( 'tr' ),
				interest      = $( '#woocommerce_cielo_credit_interest' ).closest( 'tr' );

			if ( 'store' === $( this ).val() ) {
				interest_rate.hide();
				interest.hide();
			} else {
				interest_rate.show();
				interest.show();
			}
		}).change();
	});

}( jQuery ) );
