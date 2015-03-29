<?php
/**
 * WC Cielo Debit Gateway Class.
 *
 * Built the Cielo Debit methods.
 */
class WC_Cielo_Debit_Gateway extends WC_Cielo_Helper {

	/**
	 * Cielo WooCommerce API.
	 *
	 * @var WC_Cielo_API
	 */
	public $api = null;

	/**
	 * Gateway actions.
	 */
	public function __construct() {
		$this->id           = 'cielo_debit';
		$this->icon         = apply_filters( 'wc_cielo_debit_icon', '' );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo - Debit Card', 'cielo-woocommerce' );
		$this->supports     = array( 'products', 'refunds' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title            = $this->get_option( 'title' );
		$this->description      = $this->get_option( 'description' );
		$this->store_contract   = $this->get_option( 'store_contract' );
		$this->environment      = $this->get_option( 'environment' );
		$this->number           = $this->get_option( 'number' );
		$this->key              = $this->get_option( 'key' );
		$this->methods          = $this->get_option( 'methods', 'visa' );
		$this->authorization    = $this->get_option( 'authorization' );
		$this->debit_discount   = $this->get_option( 'debit_discount' );
		$this->design           = $this->get_option( 'design' );
		$this->debug            = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = $this->get_logger();
		}

		// Set the API.
		$this->api = new WC_Cielo_API( $this );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_cielo_debit_gateway', array( $this, 'check_return' ) );
		add_action( 'woocommerce_' . $this->id . '_return', array( $this, 'return_handler' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'cielo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Cielo Debit Card', 'cielo-woocommerce' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Debit Card', 'cielo-woocommerce' )
			),
			'description' => array(
				'title'       => __( 'Description', 'cielo-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay using the secure method of Cielo', 'cielo-woocommerce' )
			),
			'store_contract' => array(
				'title'       => __( 'Store Solution', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the store contract method with cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'buypage_cielo',
				'options'     => array(
					'buypage_cielo' => __( 'BuyPage Cielo', 'cielo-woocommerce' ),
					'webservice'    => __( 'Webservice Solution', 'cielo-woocommerce' )
				)
			),
			'environment' => array(
				'title'       => __( 'Environment', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the environment type (test or production).', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'test',
				'options'     => array(
					'test'       => __( 'Test', 'cielo-woocommerce' ),
					'production' => __( 'Production', 'cielo-woocommerce' )
				)
			),
			'number' => array(
				'title'       => __( 'Affiliation Number', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Store affiliation number with Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'key' => array(
				'title'       => __( 'Affiliation Key', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Store access key assigned by Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'methods' => array(
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
			),
			'design_options' => array(
				'title'       => __( 'Design', 'cielo-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'design' => array(
				'title'   => __( 'Payment Form Design', 'cielo-woocommerce' ),
				'type'    => 'select',
				'default' => 'default',
				'options' => array(
					'default' => __( 'Default', 'cielo-woocommerce' ),
					'icons'   => __( 'With card icons', 'cielo-woocommerce' )
				)
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'cielo-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'cielo-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'cielo-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Cielo events, such as API requests, inside %s', 'cielo-woocommerce' ), $this->get_log_file_path() )
			)
		);
	}

	/**
	 * Checkout scripts.
	 */
	public function checkout_scripts() {
		if ( ! is_checkout() ) {
			return;
		}

		if ( ! $this->is_available() ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}
}
