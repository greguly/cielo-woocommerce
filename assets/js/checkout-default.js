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
			var installments = $( '#cielo-installments' ),
				methods      = [],
				debitMethods = null;

			$( '#cielo-installments' ).empty();
			$( '#cielo-installments' ).prepend( $.data( document.body, 'cielo_installments' ) );
			debitMethods = $( '.cielo-debit', installments ).attr( 'data-debit' );

			switch( debitMethods ) {
				case 'all' :
					methods = ['visa', 'mastercard'];
					break;
				case 'visa' :
					methods = ['visa'];
					break;
				case 'mastercard' :
					methods = ['mastercard'];
					break;
			}

			if ( -1 === $.inArray( card, methods ) ) {
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
