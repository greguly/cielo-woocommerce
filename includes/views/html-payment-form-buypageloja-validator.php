<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="cielo-payment-form">
	<div class="cielo-webserviceintegrada">
         <ul>
            <li class="cielo-card_number">
                <label for="card_number"><?php _e('Card number','cielo-woocommerce' );?> <span class="required">*</span></label>
                <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" VALUE="4012001037141112">
            </li>
            <li class="cielo-expiry_date">
                <label for="expiry_date"><?php _e('Expiry date','cielo-woocommerce' );?> <span class="required">*</span></label>
                <input type="text" name="expiry_date" id="expiry_date" maxlength="5" placeholder="mm/yy" value="05/18">
            </li>

            <li class="cielo-cvv">
                <label for="cvv"><?php _e('CVV','cielo-woocommerce' );?> <span class="required">*</span></label>
                <input type="text" name="cvv" id="cvv" maxlength="3" placeholder="123" value="123">
            </li>

            <!--<li class="vertical">
                <ul>
                    <li>
                        <label for="expiry_date">Expiry date</label>
                        <input type="text" name="expiry_date" id="expiry_date" maxlength="5" placeholder="mm/yy">
                    </li>

                    <li>
                        <label for="cvv">CVV</label>
                        <input type="text" name="cvv" id="cvv" maxlength="3" placeholder="123">
                    </li>
                </ul>
            </li-->
            <li>             <div class="clearfix"></div></li>
            <li class="vertical maestro">
                <ul>
                    <li>
                        <label for="issue_date"><?php _e('Issue date','cielo-woocommerce' );?><small>mm/yy</small></label>
                        <input type="text" name="issue_date" id="issue_date" maxlength="5">
                    </li>
                    <li>                     
                    	 <span class="or"><?php _e('or','cielo-woocommerce' );?></span>
                    </li>
                    <li>
                        <label for="issue_number"><?php _e('Issue number','cielo-woocommerce' );?></label>
                        <input type="text" name="issue_number" id="issue_number" maxlength="2">
                    </li>
                </ul>
            </li>

            <li>
                <label for="name_on_card"><?php _e('Name on card','cielo-woocommerce' );?> <span class="required">*</span></label>
                <input type="text" name="name_on_card" id="name_on_card" value="PAULO R D VIEIRA" placeholder="<?php _e('Please type here the name on the card','cielo-woocommerce' );?>">
            </li>
        </ul>
	</div>
	<p class="form-row form-row-first" style="display:none;">
 		<label for="cielo-card-brand"><?php _e( 'Card', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<select id="cielo-card-brand" name="cielo_card" style="font-size: 1.5em; padding: 4px; width: 100%;">
			<?php foreach ( $this->methods as $method ): ?>
				<option value="<?php echo esc_attr( $method ); ?>"><?php echo esc_attr( WC_Cielo_API::get_payment_method_name( $method ) ); ?></option>
			<?php endforeach ?>
		</select>
	</p>
	<p class="form-row form-row-last">
		<label for="cielo-installments"><?php _e( 'Installments', 'cielo-woocommerce' ); ?> <span class="required">*</span></label>
		<select id="cielo-installments" name="cielo_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">
			<?php
				$debit_methods   = WC_Cielo_API::get_debit_methods( $this->debit_methods );
				$available_debit = array_intersect( $debit_methods, $this->methods );

				if ( ! empty( $available_debit ) ) :
					$debit_total    = $cart_total * ( ( 100 - WC_Cielo_API::get_valid_value( $this->debit_discount ) ) / 100 );
					$debit_discount = ( $cart_total > $debit_total ) ? ' (' . WC_Cielo_API::get_valid_value( $this->debit_discount ) . '% ' . _x( 'off', 'price', 'cielo-woocommerce' ) . ')' : '';
				?>
				<option value="0" class="cielo-debit" data-debit="<?php echo esc_attr( $this->debit_methods ); ?>"><?php echo sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $debit_total ) ), $debit_discount ); ?></option>

			<?php endif; ?>
			<?php for ( $i = 1; $i <= $this->installments; $i++ ) :
					$credit_total    = $cart_total / $i;
					$credit_interest = __( '(no interest)', 'cielo-woocommerce' );
					$smallest_value  = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

					if ( 'client' == $this->installment_type && $i >= $this->interest ) {
						$interest_total = $credit_total * ( ( 100 + WC_Cielo_API::get_valid_value( $this->interest_rate ) ) / 100 );

						if ( $credit_total < $interest_total ) {
							$credit_total    = $interest_total;
							$credit_interest = __( '(with interest)', 'cielo-woocommerce' );
						}
					}

					if ( 1 != $i && $credit_total < $smallest_value ) {
						continue;
					}
				?>

				<option value="<?php echo $i; ?>" class="<?php echo ( 1 == $i ) ? 'cielo-at-sight' : ''; ?>"><?php echo sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest ); ?></option>

			<?php endfor; ?>
		</select>

	</p> 
	<div class="clear"></div>
</fieldset>
<script>
(function ( $ ) {
	'use strict';

	function triggerCardSelect(name){
		//console.log('card name',name);
		$('#cielo-card-brand').val(name);
		$('#cielo-card-brand').trigger('change');
	}
	$( function () {
		$( 'body' ).on( 'ajaxComplete', function () {
			$(".vertical.maestro").hide().css({
				opacity: 0
			});
			$("#card_number").validateCreditCard(function(e) {
				return $("#card_number").removeClass(), null == e.card_type ? void $(".vertical.maestro").slideUp({
					duration: 200
				}).animate({
					opacity: 0
				}, {
					queue: !1,
					duration: 200
				}) : ($("#card_number").addClass(e.card_type.name), "maestro" === e.card_type.name ? $(".vertical.maestro").slideDown({
					duration: 200
				}).animate({
					opacity: 1
				}, {
					queue: !1
				}) : $(".vertical.maestro").slideUp({
					duration: 200
				}).animate({
					opacity: 0
				}, {
					queue: !1,
					duration: 200
				}), e.length_valid && e.luhn_valid ? $("#card_number").addClass("valid") : $("#card_number").removeClass("valid")),
				e.card_type ? triggerCardSelect(e.card_type.name) : ''
			}, {
				<?php $accept = ''; foreach ( $this->methods as $method ){ $accept.='"'.esc_attr( $method ).'",';} $accept = substr($accept,0,-1);?>
				accept: [<?php echo $accept;?>]
			});
		});

	});
}( jQuery ));

</script>