<?php
/**
 * Admin options screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h3><?php echo $this->method_title; ?></h3>

<?php
if ( 'yes' == $this->get_option( 'enabled' ) ) {
	if ( ! 'BRL' == get_woocommerce_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
		include dirname( __FILE__ ) . '/notices/html-notice-currency-not-supported.php';
	}

	if ( 'test' != $this->environment && ( empty( $this->number ) || empty( $this->key ) ) ) {
		include dirname( __FILE__ ) . '/notices/html-notice-not-configured.php';
	}

	if ( 'webservice' == $this->store_contract ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2.11', '<=' ) ) {
			include dirname( __FILE__ ) . '/notices/html-notice-need-update-woocommerce.php';
		}

		if ( 'test' != $this->environment && 'no' == get_option( 'woocommerce_force_ssl_checkout' ) && ! class_exists( 'WordPressHTTPS' ) ) {
			include dirname( __FILE__ ) . '/notices/html-notice-ssl-required.php';
		}
	}
}
?>

<?php echo wpautop( $this->method_description ); ?>

<?php if ( apply_filters( 'cielo_woocommerce_help_message', true ) ) : ?>
	<div class="updated woocommerce-message inline">
		<p><?php printf( __( 'Help us keep the %s plugin free making a %s or rate %s on %s. Thank you in advance!', 'cielo-woocommerce' ), '<strong>' . __( 'Cielo WooCommerce', 'cielo-woocommerce' ) . '</strong>', '<a href="http://claudiosmweb.com/doacoes/">' . __( 'donation', 'cielo-woocommerce' ) . '</a>', '<a href="https://wordpress.org/support/view/plugin-reviews/cielo-woocommerce?filter=5#postform" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>', '<a href="https://wordpress.org/support/view/plugin-reviews/cielo-woocommerce?filter=5#postform" target="_blank">' . __( 'WordPress.org', 'cielo-woocommerce' ) . '</a>' ); ?></p>
	</div>
<?php endif; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
