<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<p class="form-row form-row-first">
		<label for="cielo-card-holder-name"><?php _e( 'Card Holder Name', 'cielo-woocommerce' ); ?> <small>(<?php _e( 'as recorded on the card', 'cielo-woocommerce' ); ?>)</small> <span class="required">*</span></label>
		<input id="cielo-card-holder-name" name="cielo_holder_name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-card-number"><?php _e( 'Card Number', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-number" name="cielo_card_number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<div class="clear"></div>
	<p class="form-row form-row-first">
		<label for="cielo-card-expiry"><?php _e( 'Expiry (MM/YYYY)', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-expiry" name="cielo_card_expiry" class="input-text wc-credit-card-form-card-expiry" type="tel" autocomplete="off" placeholder="<?php _e( 'MM / YYYY', 'cielo-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-card-cvc"><?php _e( 'Security Code', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-cvc" name="cielo_card_cvc" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e( 'CVC', 'cielo-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<?php if ( 1 < $this->installments ) : ?>
		<p class="form-row form-row-wide">

			<label for="cielo-card-installments"><?php _e( 'Installments', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
			<select id="cielo-card-installments" name="cielo_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">
				<?php
		
					$debit_methods   = WC_Cielo_API::get_debit_methods( $this->debit_methods );
					$available_debit = array_intersect( $debit_methods, $this->methods );
				
					if ( ! empty( $available_debit ) ) :
						$debit_total    = $cart_total * ( ( 100 - WC_Cielo_API::get_valid_value( $this->debit_discount ) ) / 100 );
						$debit_discount = ( $cart_total > $debit_total ) ? ' (' . WC_Cielo_API::get_valid_value( $this->debit_discount ) . '% ' . _x( 'off', 'price', 'cielo-woocommerce' ) . ')' : '';
					?>
					<option value="0" class="cielo-debit" data-debit="<?php echo esc_attr( $this->debit_methods ); ?>"><?php echo sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $debit_total ) ), $debit_discount ); ?></option>

				<?php endif; ?>
				<?php 
 					for ( $i = 1; $i <= $this->installments; $i++ ) :

 						$interest_rate = WC_Cielo_API::get_valid_value( $this->interest_rate )/100;
 						$financial_index = $interest_rate / (1- (1/pow((1+$interest_rate),$i)));

						$credit_total    = $cart_total / $i;
						$credit_interest = sprintf(__( 'no interest Total: %s', 'cielo-woocommerce' ),sanitize_text_field( woocommerce_price( $cart_total ) ));
						$smallest_value  = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

						if ( 'client' == $this->installment_type && $i >= $this->interest ) {
							$interest_total = $cart_total * $financial_index; ;//( ( 100 + WC_Cielo_API::get_valid_value( $this->interest_rate ) ) / 100 );
							$interest_cart_total = $interest_total*$i;

							if ( $credit_total < $interest_total ) {
								$credit_total    = $interest_total;
								$credit_interest = sprintf(__( '(with interest of %s%% a.m. Total: %s)', 'cielo-woocommerce' ),(WC_Cielo_API::get_valid_value( $this->interest_rate )),sanitize_text_field( woocommerce_price( $interest_cart_total ) ));
							}
						}

						if ( 1 != $i && $credit_total < $smallest_value ) {
							continue;
						}
					?>
					<?php if(1==$i){ ?>
						<option value="<?php echo $i; ?>" class="<?php echo ( 1 == $i ) ? 'cielo-at-sight' : ''; ?>"><?php echo sprintf( __( 'Credit Card %s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $credit_total ) ) ); ?></option>
					<?php }else if($i>1){ ?>
					<option value="<?php echo $i; ?>"><?php echo sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest ); ?></option>
					<?php } ?>
				<?php endfor; ?>
			</select>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
