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

<fieldset id="cielo-directdebit-payment-form" class="cielo-payment-form">

	<p><?php _e( 'Select your bank:', 'cielo-woocommerce' ); ?></p>
	<ul>
		<li>
			<label>
				<input type="radio" name="cielo_direct_debit" value="bradescodebit" />
				<i id="cielo-icon-bradesco"></i>
				<span>
					<?php _e( 'Bradesco', 'cielo-woocommerce' ); ?>
				</span>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="cielo_direct_debit" value="bancodobrasildebit" />
				<i id="cielo-icon-bancodobrasil"></i>
				<span>
					<?php _e( 'Banco do Brasil', 'cielo-woocommerce' ); ?></span>
			</label>
		</li>
	</ul>
	<p><?php _e( '* After clicking "Proceed to payment" you will have access to the link that will take you to your bank\'s website, so you can make the payment in total security.', 'cielo-woocommerce' ); ?></p>
	<div class="clear"></div>
</fieldset>
