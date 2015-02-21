<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<div class="cielo-webserviceintegrada">
	      <div class="form-group">
	        <label for="cc-number" class="control-label"><?php _e('Card number','cielo-woocommerce' );?> <small class="text-muted">[<div class="cc-brand"></div>]</small></label>
	        <input id="cc-number" type="tel" name="card_number" class="input-lg form-control cc-number" autocomplete="cc-number" placeholder="•••• •••• •••• ••••" required>
	      </div>

	      <div class="form-group">
	        <label for="cc-exp" class="control-label"><?php _e('Expiry date','cielo-woocommerce' );?> </label>
	        <input id="cc-exp" type="tel" name="expiry_date" class="input-lg form-control cc-exp" autocomplete="cc-exp" placeholder="•• / ••" required>
	      </div>

	      <div class="form-group">
	        <label for="cc-cvc" class="control-label"><?php _e('CVV','cielo-woocommerce' );?></label>
	        <input id="cc-cvc" type="tel" name="cvv" class="input-lg form-control cc-cvc" autocomplete="off" placeholder="•••" required>
	      </div>
	   
	      <div class="form-group">
         
	      	  <label for="name_on_card"><?php _e('Name on card','cielo-woocommerce' );?> <span class="required">*</span></label>
                <input type="text" name="name_on_card" id="" value="PAULO R D VIEIRA" placeholder="<?php _e('Please type here the name on the card','cielo-woocommerce' );?>">
	      </div>

	      <h2 class="validation"></h2>
 
	</div>
	<p class="form-row form-row-first" style="display:none;">
 		<label for="cielo-card-brand"><?php _e( 'Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<select id="cielo-card-brand" name="cielo_card" style="font-size: 1.5em; padding: 4px; width: 100%;">
			<?php foreach ( $this->methods as $method ): ?>
				<option value="<?php echo esc_attr( $method ); ?>"><?php echo esc_attr( WC_Cielo_API::get_payment_method_name( $method ) ); ?></option>
			<?php endforeach ?>
		</select>
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-installments"><?php _e( 'Installments', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<select id="cielo-installments" name="cielo_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">
			<?php
				$debit_methods   = WC_Cielo_API::get_debit_methods( $this->debit_methods );
				$available_debit = array_intersect( $debit_methods, $this->methods );

				if ( ! empty( $available_debit ) ) :
					$debit_total    = $cart_total * ( ( 100 - WC_Cielo_API::get_valid_value( $this->debit_discount ) ) / 100 );
					$debit_discount = ( $cart_total > $debit_total ) ? ' (' . WC_Cielo_API::get_valid_value( $this->debit_discount ) . '% ' . _x( 'off', 'price', 'cielo-woocommerce' ) . ')' : '';
				?>
				<option value="0" class="cielo-debit" data-debit="<?php echo esc_attr( $this->debit_methods ); ?>"><?php echo sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $debit_total ) ), $debit_discount ); ?></option>

			<?php endif; ?>
			<?php for ( $i = 1; $i <= $this->installments; $i++ ) :
					$credit_total    = $cart_total / $i;
					$credit_interest = __( '(no interest)', 'cielo-woocommerce' );
					$smallest_value  = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

					if ( 'client' == $this->installment_type && $i >= $this->interest ) {
						$interest_total = $credit_total * ( ( 100 + WC_Cielo_API::get_valid_value( $this->interest_rate ) ) / 100 );

						if ( $credit_total < $interest_total ) {
							$credit_total    = $interest_total;
							$credit_interest = __( '(with interest)', 'cielo-woocommerce' );
						}
					}

					if ( 1 != $i && $credit_total < $smallest_value ) {
						continue;
					}
				?>

				<option value="<?php echo $i; ?>" class="<?php echo ( 1 == $i ) ? 'cielo-at-sight' : ''; ?>"><?php echo sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest ); ?></option>

			<?php endfor; ?>
		</select>

	</p> 
	<div class="clear"></div>
</fieldset>
<script>
(function($) {
	'use strict';

	function triggerCardSelect(name) {
		//console.log('card name',name);
		$('#cielo-card-brand').val(name);
		$('.cc-brand').html('<li><i id="cielo-icon-'+name+'"></i></li>');
		$('#cielo-card-brand').trigger('change');
	}
	$(function() {
		$('body').on('ajaxComplete', function() {
			jQuery(function($) {
				$('[data-numeric]').payment('restrictNumeric');
				$('.cc-number').payment('formatCardNumber');
				$('.cc-exp').payment('formatCardExpiry');
				$('.cc-cvc').payment('formatCardCVC');

				$.fn.toggleInputError = function(erred) {
					this.parent('.form-group').toggleClass('has-error', erred);
					return this;
				};

				$('.cc-number').on('blur',function(e) {
					e.preventDefault();

					var cardType = $.payment.cardType($('.cc-number').val());
					$('.cc-number').toggleInputError(!$.payment.validateCardNumber($('.cc-number').val()));
					$('.cc-exp').toggleInputError(!$.payment.validateCardExpiry($('.cc-exp').payment('cardExpiryVal')));
					$('.cc-cvc').toggleInputError(!$.payment.validateCardCVC($('.cc-cvc').val(), cardType));
					
					cardType ? triggerCardSelect(cardType) : '';

					//console.log('cardType',cardType);
					$('.validation').removeClass('text-danger text-success');
					$('.validation').addClass($('.has-error').length ? 'text-danger' : 'text-success');
				});

			});
		});

	});
}(jQuery));
</script>