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
			include 'notices/html-notice-currency-not-supported.php';
		}

		if ( 'test' != $this->environment && ( empty( $this->number ) || empty( $this->key ) ) ) {
			include 'notices/html-notice-not-configured.php';
		}

		if ( 'webservice' == $this->store_contract ) {
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2.11', '<=' ) ) {
				include 'notices/html-notice-need-update-woocommerce.php';
			}

			if ( 'test' != $this->environment && 'no' == get_option( 'woocommerce_force_ssl_checkout' ) && ! class_exists( 'WordPressHTTPS' ) ) {
				include 'notices/html-notice-ssl-required.php';
			}
		}
	}
?>

<?php echo wpautop( $this->method_description ); ?>

<?php if ( apply_filters( 'cielo_woocommerce_help_message', true ) ) : ?>
	<div class="updated woocommerce-message">
		<p><?php printf( __( 'Do you want to use Checkout Cielo? The plugin is ready, but only for those who contribute to our %s.', 'cielo-woocommerce' ), '<a href="http://www10.vakinha.com.br/VaquinhaE.aspx?e=342130" target="_blank">' . __( 'Vakinha', 'cielo-woocommerce' ) . '</a>' ); ?></p>
	</div>
<?php endif; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
