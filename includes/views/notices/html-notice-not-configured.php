<?php
/**
 * Admin View: Notice - Not configured.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'Cielo WooCommerce Disabled', 'cielo-woocommerce' ); ?></strong>: <?php ( ("1_5" == $this->api->api->version) ? _e( 'You should inform your Affiliation Number and Key.', 'cielo-woocommerce' ) :  _e( 'You should inform your Merchant ID and Key.', 'cielo-woocommerce' ) ); ?>
	</p>
</div>
