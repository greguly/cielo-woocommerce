(function( $ ) {
	'use strict';

	$( function() {
		// Store the installment options.
		$.data( document.body, 'cielo_credit_installments', $( '#cielo-credit-payment-form #cielo-installments' ).html() );

		// Add jQuery.Payment support for Elo and Aura.
		if ( $.payment.cards ) {
			var cards = [];

			$.each( $.payment.cards, function( index, val ) {
				cards.push( val.type );
			});

			if ( -1 === $.inArray( 'elo', cards ) ) {
				$.payment.cards.push({
					type: 'elo',
					pattern: /^(636[2-3])/,
					format: /(\d{1,4})/g,
					length: [16],
					cvcLength: [3],
					luhn: true
				});
			}

			if ( -1 === $.inArray( 'aura', cards ) ) {
				$.payment.cards.unshift({
					type: 'aura',
					pattern: /^5078/,
					format: /(\d{1,6})(\d{1,2})?(\d{1,11})?/,
					length: [19],
					cvcLength: [3],
					luhn: true
				});
			}
		}

		$( 'form.checkout' ).on( 'checkout_place_order_cielo_credit', function() {
			return formHandler();
		});

		$( 'form#order_review' ).submit( function() {
			return formHandler();
		});

		$( 'body' ).on( 'checkout_error', function() {
			$( 'input.cielo-credit-card-brand' ).remove();
		});
		$( 'form.checkout, form#order_review' ).on( 'change', '#cielo-credit-payment-form input', function() {
			$( 'input.cielo-credit-card-brand' ).remove();
		});

		/**
		 * Form Handler.
		 *
		 * @return {bool}
		 */
		function formHandler() {
			if ( ! $( '#payment_method_cielo_credit' ).is( ':checked' ) ) {
				return true;
			}

			if ( 1 === jQuery( 'input.cielo-credit-card-brand' ).size() ) {
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
			$( 'input.cielo-credit-card-brand', $form ).remove();

			// Add the hash input.
			$form.append( $( '<input class="cielo-credit-card-brand" name="cielo_credit_card" type="hidden" />' ).val( card_brand ) );

			// Submit the form.
			return true;
		}

		/**
		 * Set the installment fields.
		 *
		 * @param {string} card
		 */
		function setInstallmentsFields( card ) {
			var installments = $( '#cielo-credit-payment-form #cielo-installments' );

			$( '#cielo-credit-payment-form #cielo-installments' ).empty();
			$( '#cielo-credit-payment-form #cielo-installments' ).prepend( $.data( document.body, 'cielo_credit_installments' ) );

			if ( 'discover' === card ) {
				$( 'option', installments ).not( '.cielo-at-sight' ).remove();
			}
		}

		// Set on update the checkout fields.
		$( 'body' ).on( 'ajaxComplete', function() {
			$.data( document.body, 'cielo_credit_installments', $( '#cielo-credit-payment-form #cielo-installments' ).html() );
			setInstallmentsFields( $( 'body #cielo-credit-payment-form #cielo-card-brand option' ).first().val() );
		});

		// Set on change the card brand.
		$( 'body' ).on( 'change', '#cielo-credit-payment-form #cielo-card-number', function() {
			setInstallmentsFields( $.payment.cardType( $( this ).val() ) );
		});
	});

}( jQuery ));
