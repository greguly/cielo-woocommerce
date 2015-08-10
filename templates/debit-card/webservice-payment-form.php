<?php
/**
 * Debit Card - Webservice checkout form.
 *
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<fieldset id="cielo-debit-payment-form" class="cielo-payment-form">
	<p class="form-row form-row-first">
		<label for="cielo-card-number"><?php _e( 'Card Number', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-number" name="cielo_debit_number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="22" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-card-holder-name"><?php _e( 'Name Printed on the Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-holder-name" name="cielo_debit_holder_name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<div class="clear"></div>
	<p class="form-row form-row-first">
		<label for="cielo-card-expiry"><?php _e( 'Expiry (MM/YYYY)', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-expiry" name="cielo_debit_expiry" class="input-text wc-credit-card-form-card-expiry" type="tel" autocomplete="off" placeholder="<?php _e( 'MM / YYYY', 'cielo-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-card-cvc"><?php _e( 'Security Code', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="cielo-card-cvc" name="cielo_debit_cvc" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e( 'CVC', 'cielo-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<?php if ( 0 < $discount ) : ?>
		<p class="form-row form-row-wide">
			<?php printf( __( 'Payment by debit have discount of %s. Order Total: %s.', 'cielo-woocommerce' ), $discount . '%', sanitize_text_field( woocommerce_price( $discount_total ) ) ); ?>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
