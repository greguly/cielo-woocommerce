<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<p class="form-row form-row-first">
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
				$debit_methods   = WC_Cielo_API::get_debit_methods();
				$available_debit = array_intersect( $debit_methods, $this->methods );
				if ( ! empty( $available_debit ) ) :
					$debit_total    = $cart_total * ( ( 100 - WC_Cielo_API::get_valid_value( $this->debit_discount ) ) / 100 );
					$debit_discount = ( $cart_total > $debit_total ) ? ' (' . WC_Cielo_API::get_valid_value( $this->debit_discount ) . '% ' . _x( 'off', 'price', 'cielo-woocommerce' ) . ')' : '';
				?>

				<option value="0" class="cielo-debit"><?php echo sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $debit_total ) ), $debit_discount ); ?></option>

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

					if ( $credit_total < $this->smallest_installment ) {
						continue;
					}
				?>

				<option value="<?php echo $i; ?>" class="<?php echo ( 1 == $i ) ? 'cielo-at-sight' : ''; ?>"><?php echo sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest ); ?></option>

			<?php endfor; ?>
		</select>
	</p>
	<div class="clear"></div>
</fieldset>
