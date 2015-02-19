<?php
/**
 * Admin options screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h3><?php echo ( ! empty( $this->method_title ) ) ? $this->method_title : __( 'Settings', 'cielo-woocommerce' ) ; ?></h3>

<?php echo ( ! empty( $this->method_description ) ) ? wpautop( $this->method_description ) : ''; ?>

<?php if ( apply_filters( 'cielo_woocommerce_help_message', true ) ) : ?>
	<div class="updated woocommerce-message">
		<p><?php printf( __( 'Help us to implement the Cielo Checkout and Webservice Solution. We count on your help in the form of a %s.', 'cielo-woocommerce' ), '<a href="http://www10.vakinha.com.br/VaquinhaE.aspx?e=342130" target="_blank">' . __( 'Vakinha', 'cielo-woocommerce' ) . '</a>' ); ?></p>
	</div>
<?php endif; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>

