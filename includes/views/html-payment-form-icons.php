<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<ul id="cielo-card-brand">
		<?php foreach ( $this->methods as $key => $method ): ?>
			<li><label title="<?php echo esc_attr( WC_Cielo_API::get_payment_method_name( $method ) ); ?>"><i id="cielo-icon-<?php echo esc_attr( $method ); ?>"></i><input type="radio" name="cielo_card" value="<?php echo esc_attr( $method ); ?>" <?php echo ( 0 == $key ) ? 'checked="checked"' : ''; ?>/><span><?php echo esc_attr( WC_Cielo_API::get_payment_method_name( $method ) ); ?></span></label></li>
		<?php endforeach ?>
	</ul>
	<div class="clear"></div>
	<p id="cielo-select-name"><?php _e( 'Pay with', 'cielo-woocommerce' ); ?> <strong><?php echo esc_attr( WC_Cielo_API::get_payment_method_name( current( $this->methods ) ) ); ?></strong></p>

	<div id="cielo-installments">
		<p class="form-row">
			<?php
				$debit_methods   = WC_Cielo_API::get_debit_methods( $this->debit_methods );
				$available_debit = array_intersect( $debit_methods, $this->methods );
				if ( ! empty( $available_debit ) ) :
					$debit_total    = $cart_total * ( ( 100 - WC_Cielo_API::get_valid_value( $this->debit_discount ) ) / 100 );
					$debit_discount = ( $cart_total > $debit_total ) ? ' (' . WC_Cielo_API::get_valid_value( $this->debit_discount ) . '% ' . _x( 'off', 'price', 'cielo-woocommerce' ) . ')' : '';
				?>

				<label class="cielo-debit" data-debit="<?php echo esc_attr( $this->debit_methods ); ?>"><input type="radio" name="cielo_installments" value="0" /> <?php echo sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), '<strong>' . sanitize_text_field( woocommerce_price( $debit_total ) ) . '</strong>', $debit_discount ); ?></label>

			<?php endif; ?>
			<?php
				$middle = ( ( $this->installments / 2 ) + 1 );
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
								$credit_interest = sprintf(__( 'with interest of %s%% a.m. Total: %s', 'cielo-woocommerce' ),(WC_Cielo_API::get_valid_value( $this->interest_rate )),sanitize_text_field( woocommerce_price( $interest_cart_total ) ));
							}
						}


					if ( 1 != $i && $credit_total < $smallest_value ) {
						continue;
					}

					if ( $i == $middle ) {
						//echo '</p><p class="form-row form-row-last">';
					}
				?>
				<label class="<?php echo ( 1 == $i ) ? 'cielo-at-sight' : ''; ?>"><input type="radio" name="cielo_installments" value="<?php echo $i; ?>" /> <?php echo sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, '<strong>' . sanitize_text_field( woocommerce_price( $credit_total ) ) . '</strong>', $credit_interest ); ?></label>

			<?php endfor; ?>
		</p>
	</div>

	<div class="clear"></div>
</fieldset>
