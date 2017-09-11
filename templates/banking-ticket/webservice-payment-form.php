<?php
/**
 * Credit Card - Webservice checkout form.
 *
 * @version 4.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<fieldset id="cielo-bankingticket-payment-form" class="cielo-payment-form">
	<p>
		<i id="pagseguro-icon-ticket"></i>
		<?php _e( 'The order will be confirmed only after the payment approval.', 'cielo-woocommerce' ); ?>
<!--		--><?php //if ( 'yes' === $tc_ticket_message ) : ?>
<!--			<br />-->
<!--			<strong>--><?php //_e( 'Tax', 'cielo-woocommerce' ); ?><!--:</strong> --><?php //_e( 'R$ 1,00 (rate applied to cover management risk costs of the payment method).', 'cielo-woocommerce' ); ?>
<!--		--><?php //endif; ?>
	</p>
	<p><?php _e( '* After clicking "Proceed to payment" you will have access to banking ticket which you can print and pay in your internet banking or in a lottery retailer.', 'cielo-woocommerce' ); ?></p>
	<div class="clear"></div>

</fieldset>