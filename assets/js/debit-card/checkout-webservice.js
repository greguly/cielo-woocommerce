(function( $ ) {
	'use strict';

	$( function() {
		$( 'form.checkout' ).on( 'checkout_place_order_cielo_debit', function() {
			return formHandler();
		});

		$( 'form#order_review' ).submit( function() {
			return formHandler();
		});

		$( 'body' ).on( 'checkout_error', function() {
			$( 'input.cielo-debit-card-brand' ).remove();
		});
		$( 'form.checkout, form#order_review' ).on( 'change', '#cielo-debit-payment-form input', function() {
			$( 'input.cielo-debit-card-brand' ).remove();
		});

		/**
		 * Form Handler.
		 *
		 * @return {bool}
		 */
		function formHandler() {
			if ( ! $( '#payment_method_cielo_debit' ).is( ':checked' ) ) {
				return true;
			}

			if ( 1 === jQuery( 'input.cielo-debit-card-brand' ).size() ) {
				return true;
			}

			var $form       = $( 'form.checkout, form#order_review' ),
				card_number = $( '#cielo-card-number', $form ).val(),
				card_brand  = $.payment.cardType( card_number );

			// Fixed some brand names for Cielo.
			if ( 'dinersclub' === card_brand ) {
				card_brand = 'diners';
			}

			// Remove any brand input.
			$( 'input.cielo-debit-card-brand', $form ).remove();

			// Add the hash input.
			$form.append( $( '<input class="cielo-debit-card-brand" name="cielo_debit_card" type="hidden" />' ).val( card_brand ) );

			// Submit the form.
			return true;
		}
	});

}( jQuery ));
