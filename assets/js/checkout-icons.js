(function($) {
    'use strict';

    $(function() {

        $('body').on('click', 'input[id^="payment_method_cielo"]', function() {
            $('#cielo-installments[data-id="' + $(this).val() + '"] input:eq(0)').attr('checked', 'checked');
            var card_brand = $('#cielo-card-brand[data-id="' + $(this).val() + '"] input:eq(0)');
            card_brand.attr('checked', 'checked');
            $('#cielo-select-name[data-id="' + $(this).val() + '"] strong').html('<strong>' + card_brand.parent('label').attr('title') + '</strong>');

        });
        // Set on change the card brand.
        $('body').on('click', '#cielo-card-brand input', function() {
            $('#cielo-select-name[data-id="' + $(this).closest('#cielo-card-brand').data('id') + '"] strong').html('<strong>' + $(this).parent('label').attr('title') + '</strong>');
        });
    });

}(jQuery));
