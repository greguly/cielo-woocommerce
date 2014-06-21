(function ( $ ) {
	'use strict';

	$( function () {

		// Store the options.
		$.data( document.body, 'cielo_installments', $( '#cielo-installments' ).html() );

		/**
		 * Set the installment fields.
		 *
		 * @param {string} card
		 */
		function setInstallmentsFields( card ) {
			var installments = $( '#cielo-installments' );

			$( '#cielo-installments' ).empty();
			$( '#cielo-installments' ).prepend( $.data( document.body, 'cielo_installments' ) );

			if ( 'visa' !== card && 'mastercard' !== card ) {
				$( '.cielo-debit', installments ).remove();
			}

			if ( 'discover' === card ) {
				$( 'option', installments ).not( '.cielo-at-sight' ).remove();
			}
		}

		// Set on update the checkout fields.
		$( 'body' ).on( 'ajaxComplete', function () {
			$.data( document.body, 'cielo_installments', $( '#cielo-installments' ).html() );
			setInstallmentsFields( $( 'body #cielo-card-brand option' ).first().val() );
		});

		// Set on change the card brand.
		$( 'body' ).on( 'change', '#cielo-card-brand', function () {
			setInstallmentsFields( $( ':selected', $( this ) ).val() );
		});
	});

}( jQuery ));
