<?php
/**
 * Credit Card - Default checkout form.
 *
 * @version 4.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<fieldset id="cielo-credit-payment-form" class="cielo-payment-form">
	<p class="form-row form-row-first">
		<label for="cielo-card-brand"><?php _e( 'Credit Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<select id="cielo-card-brand" name="cielo_credit_card" style="font-size: 1.5em; padding: 4px; width: 100%;">
			<?php foreach ( $methods as $method_key => $method_name ) : ?>
				<option value="<?php echo esc_attr( $method_key ); ?>"><?php echo esc_attr( $method_name ); ?></option>
			<?php endforeach ?>
		</select>
	</p>
	<?php if ( ! empty( $installments ) ) : ?>
		<p class="form-row form-row-last">
			<label for="cielo-installments"><?php _e( 'Installments', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
			<?php echo $installments; ?>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
