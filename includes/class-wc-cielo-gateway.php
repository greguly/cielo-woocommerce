<?php
/**
 * WC Cielo Gateway Class.
 *
 * Built the Cielo method.
 */
class WC_Cielo_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 *
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		$this->id			= 'cielo';
		$this->icon 		= apply_filters( 'wc_cielo_icon', plugins_url( 'assets/images/cielo.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo', 'woocommerce-cielo' );

		// Payment methods and products.
		$this->descricao_meios = array(
			'visa'       => __( 'Visa', 'woocommerce-cielo' ),
			'mastercard' => __( 'MasterCard', 'woocommerce-cielo' ),
			'diners'     => __( 'Diners', 'woocommerce-cielo' ),
			'discover'   => __( 'Discover', 'woocommerce-cielo' ),
			'elo'        => __( 'Elo', 'woocommerce-cielo' ),
			'amex'       => __( 'American Express', 'woocommerce-cielo' )
		);

		// credito a vista
		$this->meios_credito = array( 'visa', 'mastercard', 'diners', 'discover', 'elo', 'amex' );

		// credito parcelado loja
		$this->meios_credito_loja = array( 'visa', 'mastercard', 'diners', 'elo', 'amex' );

		// credito parcelado cartao
		$this->meios_credito_cartao = array( 'visa', 'mastercard', 'diners', 'elo', 'amex' );

		// debito a vista
		$this->meios_debito = array( 'visa' );

		// valor minimo da cielo para aceitar as parcelas
		$this->valor_minimo_cielo_parcela = 5;

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->environment          = $this->get_option( 'environment' );
		$this->number               = $this->get_option( 'number' );
		$this->key                  = $this->get_option( 'key' );
		$this->methods    	        = $this->get_option( 'methods' );
		$this->capture              = $this->get_option( 'capture' );
		$this->authorization        = $this->get_option( 'authorization' );
		$this->smallest_installment = $this->get_option( 'smallest_installment' );
		$this->interest_rate        = $this->get_option( 'interest_rate' );
		$this->debit_discount       = $this->get_option( 'debit_discount' );
		$this->installments         = $this->get_option( 'installments' );
		$this->interest             = $this->get_option( 'interest' );
		$this->installment_type     = $this->get_option( 'installment_type' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = parent::is_available() && 'yes' == $this->get_option( 'enabled' );

		return $available;
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-cielo' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Cielo', 'woocommerce-cielo' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-cielo' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => __( 'Cielo', 'woocommerce-cielo' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-cielo' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay using the secure method of Cielo', 'woocommerce-cielo' )
			),
			'environment' => array(
				'title'       => __( 'Environment', 'woocommerce-cielo' ),
				'type'        => 'select',
				'description' => __( 'Select the environment type (test or production).', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => 'test',
				'options'     => array(
					'test'       => __( 'Test', 'woocommerce-cielo' ),
					'production' => __( 'Production', 'woocommerce-cielo' )
				)
			),
			'number' => array(
				'title'       => __( 'Affiliation Number', 'woocommerce-cielo' ),
				'type'        => 'text',
				'description' => __( 'Store affiliation number with Cielo.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'key' => array(
				'title'       => __( 'Affiliation Key', 'woocommerce-cielo' ),
				'type'        => 'text',
				'description' => __( 'Store access key assigned by Cielo.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'methods' => array(
				'title'       => __( 'Credit Card Accepted Brands', 'woocommerce-cielo' ),
				'type'        => 'multiselect',
				'description' => __( 'Select the credit card brands that will be accepted as payment. Press the Ctrl key to select more than one brand.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => array( 'visa' ),
				'options'     => $this->descricao_meios
			),
			'capture' => array(
				'title'       => __( 'Capture automatically?', 'woocommerce-cielo' ),
				'type'        => 'select',
				'description' => __( 'Select the capture type.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => 'true',
				'options'     => array(
					'true'  => __( 'Yes', 'woocommerce-cielo' ),
					'false' => __( 'No', 'woocommerce-cielo' )
				)
			),
			'authorization' => array(
				'title'       => __( 'Automatic Authorization (Visa only)', 'woocommerce-cielo' ),
				'type'        => 'select',
				'description' => __( 'Select the authorization type.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => '2',
				'options'     => array(
					'3' => __( 'Direct authorization (does not work for debit)', 'woocommerce-cielo' ),
					'2' => __( 'Allow authorization for authenticated transaction and non-authenticated', 'woocommerce-cielo' ),
					'1' => __( 'Authorization transaction only if is authenticated', 'woocommerce-cielo' ),
					'0' => __( 'Only authenticate the transaction', 'woocommerce-cielo' )
				)
			),
			'smallest_installment' => array(
				'title'       => __( 'Smallest Installment', 'woocommerce-cielo' ),
				'type'        => 'text',
				'description' => __( 'Smallest value of each installment, cannot be less than 5', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => '5'
			),
			'interest_rate' => array(
				'title'       => __( 'Interest Rate (%)', 'woocommerce-cielo' ),
				'type'        => 'text',
				'description' => __( 'Percentage of interest that will be charged to the customer in the installment where there is interest rate to be charged.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => '2'
			),
			'debit_discount' => array(
				'title'       => __( 'Debit Discount (%)', 'woocommerce-cielo' ),
				'type'        => 'text',
				'description' => __( 'Percentage discount for payments made ​​by debit card.', 'woocommerce-cielo' ),
				'desc_tip'    => true,
				'default'     => '0'
			),
			'installments' => array(
				'title'       => __( 'Installment Within', 'woocommerce-cielo' ),
				'type'        => 'select',
				'description' => __( 'Maximum number of installments for orders in your store', 'woocommerce-cielo' ),
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
				'title'       => __( 'Without Interest Until', 'woocommerce-cielo' ),
				'type'        => 'select',
				'description' => __( 'Number of installments on which no interest will be charged from the customer.', 'woocommerce-cielo' ),
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
				'title'        => __( 'Installment Type', 'woocommerce-cielo' ),
				'type'         => 'select',
				'description'  => __( 'Store adds interest installments on the order total.', 'woocommerce-cielo' ),
				'desc_tip'     => true,
				'default'      => 'store',
				'options'     => array(
					'store'   => __( 'Store' ),
					'company' => __( 'Credit card company' )
				)
			)
		);
	}



}
