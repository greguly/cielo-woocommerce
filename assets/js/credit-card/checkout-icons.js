(function( $ ) {
	'use strict';

	$( function() {
		// Store the installment options.
		$.data( document.body, 'cielo_credit_installments', $( '#cielo-credit-payment-form #cielo-installments' ).html() );

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
				$( 'label', installments ).not( '.cielo-at-sight' ).remove();
			}

			$( 'input:eq(0)', installments ).attr( 'checked', 'checked' );
		}

		// Set on update the checkout fields.
		$( 'body' ).on( 'ajaxComplete', function() {
			$.data( document.body, 'cielo_credit_installments', $( '#cielo-credit-payment-form #cielo-installments' ).html() );
			setInstallmentsFields( $( 'body #cielo-credit-payment-form #cielo-card-brand input' ).first().val() );
		});

		// Set on change the card brand.
		$( 'body' ).on( 'click', '#cielo-credit-payment-form #cielo-card-brand input', function() {
			$( '#cielo-credit-payment-form #cielo-select-name strong' ).html( '<strong>' + $( this ).parent( 'label' ).attr( 'title' ) + '</strong>' );
			setInstallmentsFields( $( this ).val() );
		});
	});

}( jQuery ));
