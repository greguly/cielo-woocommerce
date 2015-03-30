<?php
/**
 * Debit Card - Icons checkout form.
 *
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$first_method = current( $methods );

?>

<fieldset id="cielo-debit-payment-form" class="cielo-payment-form">
	<?php if ( 1 < count( $methods ) ) : ?>
		<ul id="cielo-card-brand">
			<?php foreach ( $methods as $method_key => $method_name ): ?>
				<li><label title="<?php echo esc_attr( $method_name ); ?>"><i id="cielo-icon-<?php echo esc_attr( $method_key ); ?>"></i><input type="radio" name="cielo_debit_card" value="<?php echo esc_attr( $method_key ); ?>" <?php echo ( $first_method == $method_name ) ? 'checked="checked"' : ''; ?>/><span><?php echo esc_attr( $method_name ); ?></span></label></li>
			<?php endforeach ?>
		</ul>
	<?php else : ?>
		<p><?php printf( __( 'Pay with %s.', 'cielo-woocommerce' ), current( $methods ) ); ?></p>
		<input type="hidden" name="cielo_debit_card" value="<?php echo esc_attr( key( $methods ) ); ?>" />
	<?php endif; ?>
	<?php if ( 0 < $discount ) : ?>
		<p class="form-row form-row-wide">
			<?php printf( __( 'Payment by debit have discount of %s. Order Total: %s.', 'cielo-woocommerce' ), $discount . '%', sanitize_text_field( woocommerce_price( $discount_total ) ) ); ?>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
