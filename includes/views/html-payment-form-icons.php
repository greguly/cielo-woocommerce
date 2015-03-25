<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<ul id="cielo-card-brand">
		<?php foreach ( $this->methods as $key => $method ): ?>
			<li><label title="<?php echo esc_attr( WC_Cielo_Helper::get_payment_method_name( $method ) ); ?>"><i id="cielo-icon-<?php echo esc_attr( $method ); ?>"></i><input type="radio" name="cielo_card" value="<?php echo esc_attr( $method ); ?>" <?php echo ( 0 == $key ) ? 'checked="checked"' : ''; ?>/><span><?php echo esc_attr( WC_Cielo_Helper::get_payment_method_name( $method ) ); ?></span></label></li>
		<?php endforeach ?>
	</ul>
	<div class="clear"></div>
	<p id="cielo-select-name"><?php _e( 'Pay with', 'cielo-woocommerce' ); ?> <strong><?php echo esc_attr( WC_Cielo_Helper::get_payment_method_name( current( $this->methods ) ) ); ?></strong></p>

	<div id="cielo-installments">
		<p class="form-row">
			<?php echo $this->helper->get_installments_html( 'radio', $order_total ); ?>
		</p>
	</div>

	<div class="clear"></div>
</fieldset>
