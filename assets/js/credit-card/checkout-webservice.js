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
