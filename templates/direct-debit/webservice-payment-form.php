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

<fieldset id="cielo-directdebit-payment-form" class="cielo-payment-form" style="display: block;">

	<p><?php _e( 'Select your bank:', 'cielo-woocommerce' ); ?></p>
	<ul style="list-style: none; margin: 0 0 10px; padding: 0;">
        <style type="text/css">
            input[name="cielo_direct_debit"]:checked {
                outline: 1px solid red
            }
        </style>
		<li style="border: none; display: block; float: left; list-style: none; margin: 0; padding: 0; width: 130px;">
			<label style="cursor: pointer; display: block; font-weight: bold; margin: 0; padding: 5px 10px; text-align: center;">
				<input type="radio" name="cielo_direct_debit" value="Bradesco" style="display: none;" />
				<i id="cielo-icon-bradesco" style="background-color: transparent; background-image: url(https://francompras.com.br/wp-content/plugins/cielo-woocommerce/assets/images/transparent-checkout-icons.png); background-repeat: no-repeat; display: block; height: 51px; margin: 0 auto; width: 51px;"></i>
				<span>
					<?php _e( 'Bradesco', 'cielo-woocommerce' ); ?>
				</span>
			</label>
		</li>
		<li style="border: none; display: block; float: left; list-style: none; margin: 0; padding: 0; width: 130px;">
			<label style="cursor: pointer; display: block; font-weight: bold; margin: 0; padding: 5px 10px; text-align: center;">
				<input type="radio" name="cielo_direct_debit" value="Banco do Brasil" style="display: none;" />
				<i id="cielo-icon-bancodobrasil" style="background-color: transparent; background: url(https://francompras.com.br/wp-content/plugins/cielo-woocommerce/assets/images/transparent-checkout-icons.png) 20% 0; background-repeat: no-repeat; display: block; height: 51px; margin: 0 auto; width: 51px;"></i>
				<span>
					<?php _e( 'Banco do Brasil', 'cielo-woocommerce' ); ?></span>
			</label>
		</li>
	</ul>
    <br>
	<p style="margin-bottom: .5em; padding: 100px 0 0 0;"><?php _e( '* After clicking "Proceed to payment" you will have access to the link that will take you to your bank\'s website, so you can make the payment in total security.', 'cielo-woocommerce' ); ?></p>
	<div class="clear"></div>
</fieldset>
