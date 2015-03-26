<?php
/**
 * WC Cielo Credit Gateway Class.
 *
 * Built the Cielo Credit methods
 */


class WC_Cielo_Credit_Gateway extends WC_Cielo_Gateway {

	public function __construct() {
	 	parent::__construct('cielo_credit',__('Cielo Credit','cielo-woocommerce'));
	 	$this->debit_methods='none';
	 }
	public function init_form_credit_fields() {
		$debit_fields = array(

			'methods' => array(
				'title'       => __( 'Accepted Card Brands', 'cielo-woocommerce' ),
				'type'        => 'multiselect',
				'description' => __( 'Select the card brands that will be accepted as payment. Press the Ctrl key to select more than one brand.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => array( 'visa', 'mastercard' ),
				'options'     => WC_Cielo_Helper::get_payment_methods()
			),
			'authorization' => array(
				'title'       => __( 'Automatic Authorization (MasterCard and Visa only)', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the authorization type.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '2',
				'options'     => array(
					'3' => __( 'Direct authorization', 'cielo-woocommerce' ),
					'2' => __( 'Allow authorization for authenticated transaction and non-authenticated', 'cielo-woocommerce' ),
					'1' => __( 'Authorization transaction only if is authenticated', 'cielo-woocommerce' ),
					'0' => __( 'Only authenticate the transaction', 'cielo-woocommerce' )
				)
			),
			'smallest_installment' => array(
				'title'       => __( 'Smallest Installment', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Smallest value of each installment, cannot be less than 5.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '5'
			),
			'interest_rate' => array(
				'title'       => __( 'Interest Rate (%)', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Percentage of interest that will be charged to the customer in the installment where there is interest rate to be charged.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '2'
			),
			'installments' => array(
				'title'       => __( 'Installment Within', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Maximum number of installments for orders in your store.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '1',
				'options'     => array(
					'1'  => '1x',
					'2'  => '2x',
					'3'  => '3x',
					'4'  => '4x',
					'5'  => '5x',
					'6'  => '6x',
					'7'  => '7x',
					'8'  => '8x',
					'9'  => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x'
				)
			),
			'interest' => array(
				'title'       => __( 'Charge Interest Since', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Indicate from which installment should be charged interest.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '6',
				'options'     => array(
					'1'  => '1x',
					'2'  => '2x',
					'3'  => '3x',
					'4'  => '4x',
					'5'  => '5x',
					'6'  => '6x',
					'7'  => '7x',
					'8'  => '8x',
					'9'  => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x'
				)
			),
			'installment_type' => array(
				'title'        => __( 'Installment Type', 'cielo-woocommerce' ),
				'type'         => 'select',
				'description'  => __( 'Client adds interest installments on the order total.', 'cielo-woocommerce' ),
				'desc_tip'     => true,
				'default'      => 'client',
				'options'      => array(
					'client' => __( 'Client', 'cielo-woocommerce' ),
					'store'  => __( 'Store', 'cielo-woocommerce' )
				)
			)
		);

		return $debit_fields;
	}

		public function init_form_fields() {
 		parent::init_form_fields();

 		$this->form_fields = array_merge($this->form_fields,$this->init_form_credit_fields());
 		$this->form_fields = array_merge($this->form_fields,parent::init_form_layout_fields());
 		$this->form_fields = array_merge($this->form_fields,parent::init_form_debug_fields());
 	}

}

 