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
				$( 'label', installments ).not( '.cielo-at-sight' ).remove();
			}
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
