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
				$( 'label', installments ).not( '.cielo-at-sight' ).remove();
			}

			$( 'input:eq(0)', installments ).attr( 'checked', 'checked' );
		}

		// Set on update the checkout fields.
		$( 'body' ).on( 'ajaxComplete', function () {
			$.data( document.body, 'cielo_installments', $( '#cielo-installments' ).html() );
			setInstallmentsFields( $( 'body #cielo-card-brand input' ).first().val() );
		});

		// Set on change the card brand.
		$( 'body' ).on( 'click', '#cielo-card-brand input', function () {
			$( '#cielo-select-name strong' ).html( '<strong>' + $( this ).parent( 'label' ).attr( 'title' ) + '</strong>' );
			setInstallmentsFields( $( this ).val() );
		});
	});

}( jQuery ));
