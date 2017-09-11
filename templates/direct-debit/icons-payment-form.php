<?php
/**
 * Credit Card - Icons checkout form.
 *
 * @version 4.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$first_method = current( $methods );

?>

<fieldset id="cielo-credit-payment-form" class="cielo-payment-form">
	<ul id="cielo-card-brand">
		<?php foreach ( $methods as $method_key => $method_name ): ?>
			<li><label title="<?php echo esc_attr( $method_name ); ?>"><i id="cielo-icon-<?php echo esc_attr( $method_key ); ?>"></i><input type="radio" name="cielo_credit_card" value="<?php echo esc_attr( $method_key ); ?>" <?php echo ( $first_method == $method_name ) ? 'checked="checked"' : ''; ?>/><span><?php echo esc_attr( $method_name ); ?></span></label></li>
		<?php endforeach ?>
	</ul>

	<div class="clear"></div>

	<?php if ( ! empty( $installments ) ) : ?>
		<p id="cielo-select-name"><?php _e( 'Pay with', 'cielo-woocommerce' ); ?> <strong><?php echo esc_attr( $first_method ); ?></strong></p>

		<div id="cielo-installments">
			<p class="form-row">
				<?php echo $installments; ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="clear"></div>
</fieldset>
