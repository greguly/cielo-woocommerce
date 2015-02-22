/* global wc_cielo_checkout_webservice_params */
/*jshint devel: true */
(function ( $ ) {
	'use strict';

	$( function () {

		var cielo_submit = false;

		// Add jQuery.Payment support for Elo and Aura.
		if ( $.payment.cards ) {
			var cards = [];

			$.each($.payment.cards, function(index, val) {
				cards.push(val.type);
			});

			if ( -1 === $.inArray( 'elo', cards ) ) {
				$.payment.cards.push({
					type: 'elo',
					pattern: /^(636[2-3])/,
					length: [16],
					cvcLength: [3],
					luhn: true,
					format: /(\d{1,4})/g
				});
			}

			if ( -1 === $.inArray( 'aura', cards ) ) {
				$.payment.cards.unshift({
					type: 'aura',
					pattern: /^5078/,
					length: [19],
					cvcLength: [3],
					luhn: true,
					format: /(\d{1,4})/g
				});
			}
		}

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

			// Fixed some brand names for Cielo.
			if ( 'dinersclub' === card_brand ) {
				card_brand = 'diners';
			}
			if ( 'visaelectron' === card_brand ) {
				card_brand = 'visa';
			}
			if ( 'maestro' === card_brand ) {
				card_brand = 'mastercard';
			}

			// Check the card brand is available.
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
