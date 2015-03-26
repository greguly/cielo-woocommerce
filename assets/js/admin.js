(function($) {
    'use strict';

    $(function() {



        /**
         * Switch the options based on the store contract.
         */
        $('[id^=woocommerce_cielo][id$=store_contract]').on('change', function() {
            //#mainform h3:eq(1), #mainform .form-table:eq(1)'),
 
            var design = $('[id^=woocommerce_cielo][id$=_design]').closest('tr'),
                merchant_id = $('[id^=woocommerce_cielo][id$=merchant_id]').closest('tr'),
                cielo_number = $('[id^=woocommerce_cielo][id$=number]').closest('tr'),
                cielo_key = $('[id^=woocommerce_cielo][id$=key]').closest('tr'),
                antifraud = $('[id^=woocommerce_cielo][id$=antifraud]').closest('tr');

            switch ($(this).val()) {
                case 'webservice':
                    design.hide();
                    merchant_id.hide();
                    cielo_number.show();
                    cielo_key.show();
                    antifraud.hide();
                    break;
                case 'checkout_cielo':
                    design.show();
                    merchant_id.show();
                    cielo_number.hide();
                    cielo_key.hide();
                    antifraud.show();
                    break;
                default: //buypage cielo
                    design.show();
                    merchant_id.hide();
                    cielo_number.show();
                    cielo_key.show();
                    antifraud.hide();
                    break;
            }
        }).change();

     

        /**
         * Switch the options based on environment.
         */
        $('[id^=woocommerce_cielo][id$=environment]').on('change', function() {
            var number = $('[id^=woocommerce_cielo][id$=number]').closest('tr'),
                key = $('[id^=woocommerce_cielo][id$=key]').closest('tr');

            if ('test' === $(this).val()) {
                number.hide();
                key.hide();
            } else {
                number.show();
                key.show();
            }
        }).change();


    });

}(jQuery));
