<?php
/**
 * Admin View: Notice - Not configured.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( isset( $_POST[ $id . 'environment' ] ) && 'test' == $_POST[ $id . 'environment' ] ) || ( isset( $_POST[ $id . 'number' ] ) && ! empty( $_POST[ $id . 'number' ] ) && isset( $_POST[ $id . 'key' ] ) && ! empty( $_POST[ $id . 'key' ] ) ) ) {
	return;
}

?>

<div class="error">
	<p><strong><?php _e( 'Cielo WooCommerce Disabled', 'cielo-woocommerce' ); ?></strong>: <?php _e( 'You should inform your Affiliation Number and Key.', 'cielo-woocommerce' ); ?>
	</p>
</div>
