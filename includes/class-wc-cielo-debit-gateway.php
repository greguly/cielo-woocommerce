<?php
/**
 * WC Cielo Debit Gateway Class.
 *
 * Built the Cielo Credit methods
 */


class WC_Cielo_Debit_Gateway extends WC_Cielo_Gateway {

	public function __construct() {
	 	parent::__construct('cielo_debit',__('Cielo Debit','cielo-woocommerce'));
	 }
 	/**
	 *  Gateway Settings Form Fields related specifically to layout/design
	 */
	public function init_form_debit_fields() {
		$debit_fields = array(
			'debit_methods' => array(
				'title'       => __( 'Accepted Debit Cards', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the debit card that will be accepted as payment.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'visa',
				'options'     => array(
					'visa'       => __( 'Visa only', 'cielo-woocommerce' ),
					'mastercard' => __( 'MasterCard only', 'cielo-woocommerce' ),
					'all'        => __( 'All debit methods', 'cielo-woocommerce' )
				)
			),
			'authorization' => array(
				'title'       => __( 'Automatic Authorization (MasterCard and Visa only)', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the authorization type.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '2',
				'options'     => array(
					'2' => __( 'Allow authorization for authenticated transaction and non-authenticated', 'cielo-woocommerce' ),
					'1' => __( 'Authorization transaction only if is authenticated', 'cielo-woocommerce' ),
					'0' => __( 'Only authenticate the transaction', 'cielo-woocommerce' )
				)
			),
			'debit_discount' => array(
				'title'       => __( 'Debit Discount (%)', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Percentage discount for payments made ​​by debit card.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '0'
			)
		);

		return $debit_fields;
	}
 	public function init_form_fields() {
 		parent::init_form_fields();

 		$this->form_fields = array_merge($this->form_fields,$this->init_form_debit_fields());
 		$this->form_fields = array_merge($this->form_fields,parent::init_form_layout_fields());
 		$this->form_fields = array_merge($this->form_fields,parent::init_form_debug_fields());
 	}

}

 	