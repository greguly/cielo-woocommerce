<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<p class="form-row form-row-first">
		<label for="cielo-card-brand"><?php _e( 'Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<select id="cielo-card-brand" name="cielo_card" style="font-size: 1.5em; padding: 4px; width: 100%;">
			<?php foreach ( $this->methods as $method ) : ?>
				<option value="<?php echo esc_attr( $method ); ?>"><?php echo esc_attr( WC_Cielo_Helper::get_payment_method_name( $method ) ); ?></option>
			<?php endforeach ?>
		</select>
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-installments"><?php _e( 'Installments', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<?php echo $this->helper->get_installments_html( 'select', $order_total ); ?>
	</p>
	<div class="clear"></div>
</fieldset>
