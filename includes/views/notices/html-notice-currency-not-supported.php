<?php
/**
 * Admin View: Notice - Currency not supported.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'Cielo WooCommerce Disabled', 'cielo-woocommerce' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported. Works only with Brazilian Real.', 'cielo-woocommerce' ), get_woocommerce_currency() ); ?>
	</p>
</div>
