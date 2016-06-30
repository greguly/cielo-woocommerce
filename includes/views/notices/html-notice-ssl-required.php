<?php
/**
 * Admin View: Notice - SSL Required.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'Cielo WooCommerce Disabled', 'cielo-woocommerce' ); ?></strong>: <?php printf( __( 'A SSL Certificate is required for Webservice Solution. Please verify if a certificate is installed on your server and enable the %s option.', 'cielo-woocommerce' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section' ) ) . '">' . __( 'Force secure checkout', 'cielo-woocommerce' ) . '</a>' ); ?>
	</p>
</div>
