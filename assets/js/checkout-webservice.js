/* global wc_cielo_checkout_webservice_params */
/*jshint devel: true */
(function ( $ ) {
	'use strict';

	$( function () {

		var cielo_submit = false;

		$( 'form.checkout' ).on( 'checkout_place_order_cielo', function() {
			return formHandler( this );
		});

		$( 'form#order_review' ).submit( function() {
			return formHandler( this );
		});

		$( 'body' ).on( 'checkout_error', function() {
			$( '.cielo-card-brand' ).remove();
		});
		$( 'form.checkout, form#order_review' ).on( 'change', '#cielo-payment-form input', function() {
			$( '.cielo-card-brand' ).remove();
		});

		/**
		 * Form Handler.
		 *
		 * @param  {object} form
		 *
		 * @return {bool}
		 */
		function formHandler( form ) {
			if ( cielo_submit ) {
				cielo_submit = false;

				return true;
			}

			if ( ! $( '#payment_method_cielo' ).is( ':checked' ) ) {
				return true;
			}

			var $form       = $( form ),
				card_number = $( '#cielo-card-number', $form ).val(),
				card_brand  = $.payment.cardType( card_number );

			// Fixed the diners name.
			if ( 'dinersclub' === card_brand ) {
				card_brand = 'diners';
			}

			if ( -1 !== $.inArray( card_brand, wc_cielo_checkout_webservice_params.available_brands ) ) {
				// Remove any brand input.
				$( '.cielo-card-brand', $form ).remove();

				// Add the hash input.
				$form.append( $( '<input class="cielo-card-brand" name="cielo_card" type="hidden" />' ).val( card_brand ) );

				// Submit the form.
				cielo_submit = true;
				$form.submit();
			}

			return true;
		}
	});

}( jQuery ));
