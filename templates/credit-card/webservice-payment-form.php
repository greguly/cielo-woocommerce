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

<fieldset id="cielo-credit-payment-form" class="cielo-payment-form">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="cielo-card-number"><?php _e( 'Card Number', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
                <input id="cielo-card-number" name="cielo_credit_number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="22" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="width: 100%; font-size: 1.5em; padding: 8px;" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cielo-card-holder-name"><?php _e( 'Name Printed on the Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
                <input id="cielo-card-holder-name" name="cielo_credit_holder_name" class="input-text" type="text" autocomplete="off" style="width: 100%; font-size: 1.5em; padding: 8px;" />
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
<!--                        <input id="cielo-card-expiry" name="cielo_credit_expiry" class="input-text wc-credit-card-form-card-expiry" type="tel" autocomplete="off" placeholder="--><?php //_e( 'MM / YYYY', 'cielo-woocommerce' ); ?><!--" style="width: 100%; font-size: 1.5em; padding: 8px;" />-->
                        <select id="cielo-card-expiry-month" name="cielo_credit_expiry_month" class="input-text wc-credit-card-form-card-expiry-month" autocomplete="off" style="font-size: 1.5em; padding: 7px; width: 100%;">
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
<!--                        <input id="cielo-card-expiry-year" name="cielo_credit_expiry-year" class="input-text wc-credit-card-form-card-expiry-year" type="tel" autocomplete="off" placeholder="--><?php //_e( 'MM / YYYY', 'cielo-woocommerce' ); ?><!--" style="width: 100%; font-size: 1.5em; padding: 8px;" />-->
                        <select id="cielo-card-expiry-year" name="cielo_credit_expiry_year" class="input-text wc-credit-card-form-card-expiry-year" autocomplete="off" style="font-size: 1.5em; padding: 7px; width: 100%;">
                            <option>Ano</option>
                            <?php for ( $i = date('Y', strtotime('-1 year')); $i <= date('Y', strtotime('+11 year')); $i++ ) { ?>
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
                <input id="cielo-card-cvc" name="cielo_credit_cvc" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e( 'CVC', 'cielo-woocommerce' ); ?>" style="width: 100%; font-size: 1.5em; padding: 8px;" />
            </div>
        </div>
    </div>
	<?php if ( ! empty( $installments ) ) : ?>
		<p class="form-row form-row-wide">
			<label for="cielo-installments"><?php _e( 'Installments', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
			<?php echo $installments; ?>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
