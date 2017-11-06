(function( $ ) {
    'use strict';

    // Store the installment options.
    $.data( document.body, 'cielo_credit_installments', $( '#cielo-credit-payment-form #cielo-installments' ).html() );

    var installments_update = false;

    /**
     * Set the installment fields.
     *
     * @param {String} card
     */
    function setInstallmentsFields( card ) {
        var installments = $( '#cielo-credit-payment-form #cielo-installments' );

        $( '#cielo-credit-payment-form #cielo-installments' ).empty();
        $( '#cielo-credit-payment-form #cielo-installments' ).prepend( $.data( document.body, 'cielo_credit_installments' ) );

        if ( 'discover' === card ) {
            $( 'option', installments ).not( '.cielo-at-sight' ).remove();
        }
    }

    $( document.body ).on( 'ajaxStart', function() {


    }).on( 'ajaxComplete', function() {

        if ($.data( document.body, 'credit_installments') !== undefined) {

            installments_update = true;
            $( '#cielo-credit-payment-form #cielo-card-number' ).val( $.data( document.body, 'credit_card_number' ) ).change();
            $( '#cielo-credit-payment-form #cielo-card-holder-name' ).val( $.data( document.body, 'credit_card_holder_name' ) ).change();
            $( '#cielo-credit-payment-form #cielo-card-expiry-month' ).val( $.data( document.body, 'credit_card_expiry_month' ) ).change();
            $( '#cielo-credit-payment-form #cielo-card-expiry-year' ).val( $.data( document.body, 'credit_card_expiry_year' ) ).change();
            $( '#cielo-credit-payment-form #cielo-installments' ).val( $.data( document.body, 'credit_installments' ) ).change();
            installments_update = false;

            $.data( document.body, 'credit_card_number', undefined);
            $.data( document.body, 'credit_card_holder_name', undefined);
            $.data( document.body, 'credit_card_expiry_month', undefined);
            $.data( document.body, 'credit_card_expiry_year', undefined);
            $.data( document.body, 'credit_installments', undefined);

        } else {

            $.data(document.body, 'cielo_credit_installments', $('#cielo-credit-payment-form #cielo-installments').html());
            setInstallmentsFields( $( 'body #cielo-credit-payment-form #cielo-card-brand option' ).first().val() );

        }

    });

    // Set on change the card brand.
    $( document.body )
        .on( 'change', '#cielo-credit-payment-form #cielo-installments', function() {

            if (!installments_update) {

                var card_number = $( '#cielo-credit-payment-form #cielo-card-number' ).val();
                var holder_name = $( '#cielo-credit-payment-form #cielo-card-holder-name' ).val();
                var expiry_month = $( '#cielo-credit-payment-form #cielo-card-expiry-month' ).val();
                var expiry_year = $( '#cielo-credit-payment-form #cielo-card-expiry-year' ).val();
                var installment = $('#cielo-credit-payment-form #cielo-installments').val();

                $.data( document.body, 'credit_card_number', card_number);
                $.data( document.body, 'credit_card_holder_name', holder_name);
                $.data( document.body, 'credit_card_expiry_month', expiry_month);
                $.data( document.body, 'credit_card_expiry_year', expiry_year);
                $.data(document.body, 'credit_installments', installment);

                $('body').trigger('update_checkout');

            }

        });

}( jQuery ));