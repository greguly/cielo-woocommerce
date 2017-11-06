<?php
/**
 * Debit Card - Webservice checkout form.
 *
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<fieldset id="cielo-debit-payment-form" class="cielo-payment-form">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="cielo-card-number"><?php _e( 'Card Number', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
                <input id="cielo-card-number" name="cielo_debit_number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="22" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="width: 100%; font-size: 1.5em; padding: 8px;" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cielo-card-holder-name"><?php _e( 'Name Printed on the Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
                <input id="cielo-card-holder-name" name="cielo_debit_holder_name" class="input-text" type="text" autocomplete="off" style="width: 100%; font-size: 1.5em; padding: 8px;" />
            </div>
        </div>
    </div>
	<div class="clear"></div>
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cielo-card-expiry-month"><?php _e( 'Mes Expiracao', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
<!--                        <input id="" name="" class="input-text wc-credit-card-form-card-expiry-month" type="tel" autocomplete="off" placeholder="--><?php //_e( 'MM / YYYY', 'cielo-woocommerce' ); ?><!--" style="font-size: 1.5em; padding: 8px;" />-->
                        <select id="cielo-card-expiry-month" name="cielo_debit_expiry_month" class="input-text wc-credit-card-form-card-expiry-month" autocomplete="off" style="font-size: 1.5em; padding: 7px; width: 100%;">
                            <option>Mes</option>
                            <?php for ( $i = 1; $i <= 12; $i++ ) { ?>
                                <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="cielo-card-expiry-year"><?php _e( 'Ano Expiracao', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
<!--                        <input id="" name="" class="input-text " type="tel" autocomplete="off" placeholder="--><?php //_e( 'MM / YYYY', 'cielo-woocommerce' ); ?><!--" style="font-size: 1.5em; padding: 8px;" />-->
                        <select id="cielo-card-expiry-year" name="cielo_debit_expiry_year" class="input-text wc-credit-card-form-card-expiry-year" autocomplete="off" style="font-size: 1.5em; padding: 7px; width: 100%;">
                            <option>Ano</option>
                            <?php for ( $i = date('Y', strtotime('0 year')); $i <= date('Y', strtotime('+15 year')); $i++ ) { ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cielo-card-cvc"><?php _e( 'Security Code', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
                <input id="cielo-card-cvc" name="cielo_debit_cvc" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e( 'CVC', 'cielo-woocommerce' ); ?>" style="width: 100%; font-size: 1.5em; padding: 8px;" />
            </div>
        </div>
    </div>
	<?php if ( 0 < $discount ) : ?>
		<p class="form-row form-row-wide">
			<?php printf( __( 'Payment by debit have discount of %s. Order Total: %s.', 'cielo-woocommerce' ), $discount . '%', sanitize_text_field( woocommerce_price( $discount_total ) ) ); ?>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
