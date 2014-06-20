<?php
/**
 * WC Cielo Gateway Class.
 *
 * Built the Cielo method.
 */
class WC_Cielo_Gateway extends WC_Payment_Gateway {

	/**
	 * Cielo WooCommerce API.
	 *
	 * @var WC_Cielo_API
	 */
	protected $api = null;

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
		$this->method_title = __( 'Cielo', 'cielo-woocommerce' );

		// Payment methods and products.
		$this->descricao_meios = array(
			'visa'       => __( 'Visa', 'cielo-woocommerce' ),
			'mastercard' => __( 'MasterCard', 'cielo-woocommerce' ),
			'diners'     => __( 'Diners', 'cielo-woocommerce' ),
			'discover'   => __( 'Discover', 'cielo-woocommerce' ),
			'elo'        => __( 'Elo', 'cielo-woocommerce' ),
			'amex'       => __( 'American Express', 'cielo-woocommerce' )
		);

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
		$this->debug                = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = $woocommerce->logger();
			}
		}

		// Set the API.
		$this->api = new WC_Cielo_API( $this );

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
				'title'   => __( 'Enable/Disable', 'cielo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Cielo', 'cielo-woocommerce' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Cielo', 'cielo-woocommerce' )
			),
			'description' => array(
				'title'       => __( 'Description', 'cielo-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay using the secure method of Cielo', 'cielo-woocommerce' )
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
				'title'       => __( 'Credit Card Accepted Brands', 'cielo-woocommerce' ),
				'type'        => 'multiselect',
				'description' => __( 'Select the credit card brands that will be accepted as payment. Press the Ctrl key to select more than one brand.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => array( 'visa' ),
				'options'     => WC_Cielo_API::get_payment_methods()
			),
			'capture' => array(
				'title'       => __( 'Capture automatically?', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the capture type.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'true',
				'options'     => array(
					'true'  => __( 'Yes', 'cielo-woocommerce' ),
					'false' => __( 'No', 'cielo-woocommerce' )
				)
			),
			'authorization' => array(
				'title'       => __( 'Automatic Authorization (Visa only)', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the authorization type.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '2',
				'options'     => array(
					'3' => __( 'Direct authorization (does not work for debit)', 'cielo-woocommerce' ),
					'2' => __( 'Allow authorization for authenticated transaction and non-authenticated', 'cielo-woocommerce' ),
					'1' => __( 'Authorization transaction only if is authenticated', 'cielo-woocommerce' ),
					'0' => __( 'Only authenticate the transaction', 'cielo-woocommerce' )
				)
			),
			'smallest_installment' => array(
				'title'       => __( 'Smallest Installment', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Smallest value of each installment, cannot be less than 5', 'cielo-woocommerce' ),
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
			'debit_discount' => array(
				'title'       => __( 'Debit Discount (%)', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Percentage discount for payments made ​​by debit card.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '0'
			),
			'installments' => array(
				'title'       => __( 'Installment Within', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Maximum number of installments for orders in your store', 'cielo-woocommerce' ),
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
				'options'     => array(
					'client' => __( 'Client', 'cielo-woocommerce' ),
					'store'  => __( 'Store', 'cielo-woocommerce' )
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
				'description' => sprintf( __( 'Log Cielo events, such as API requests, inside %s', 'cielo-woocommerce' ), '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>' )
			)
		);
	}

	/**
	 * Add error messages in checkout.
	 *
	 * @param  string $message Error message.
	 *
	 * @return void
	 */
	protected function add_error( $message ) {
		global $woocommerce;

		$title = '<strong>' . __( 'Cielo', 'cielo-woocommerce' ) . ':</strong> ';

		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $title . $message, 'error' );
		} else {
			$woocommerce->add_error( $title . $message );
		}
	}

	/**
	 * Payment fields.
	 *
	 * @return string
	 */
	public function payment_fields() {
		global $woocommerce;

		wp_enqueue_script( 'wc-credit-card-form' );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$cart_total = (float) WC()->cart->total;
		} else {
			$cart_total = (float) $woocommerce->cart->total;
		}

		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		include_once( 'views/html-payment-form.php' );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) {
		$order        = new WC_Order( $order_id );
		$card         = isset( $_POST['cielo_card'] ) ? sanitize_text_field( $_POST['cielo_card'] ) : '';
		$installments = isset( $_POST['cielo_installments'] ) ? absint( $_POST['cielo_installments'] ) : '';
		$valid        = true;
		$payment_url  = '';

		// Validate the card brand.
		if ( ! in_array( $card, $this->methods ) ) {
			$this->add_error( __( 'please select a card.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate the installments field.
		if ( '' === $installments ) {
			$this->add_error( __( 'please select a number of installments.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate if debit is available.
		if ( ! in_array( $card, WC_Cielo_API::get_debit_methods() ) && 0 == $installments ) {
			$this->add_error( sprintf( __( '%s does not accept payment by debit.', 'cielo-woocommerce' ), WC_Cielo_API::get_payment_method_name( $card ) ) );
			$valid = false;
		}

		// Validate the installments amount.
		$installment_total = $order->order_total / $installments;
		if ( 'client' == $this->installment_type && $installments >= $this->interest ) {
			$interest_total    = $installment_total * ( ( 100 + $this->interest_rate ) / 100 );
			$installment_total = ( $installment_total < $interest_total ) ? $interest_total : $installment_total;
		}
		$smallest_value = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;
		if ( $installments > $this->installments || $installment_total < $smallest_value ) {
			$this->add_error( __( 'invalid number of installments!', 'cielo-woocommerce' ) );
			$valid = false;
		}

		if ( $valid ) {
			$response = $this->api->do_transaction( $order, time(), $card, $installments );

			// Set the error alert.
			if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
				$this->add_error( (string) $response->mensagem );
				$valid = false;
			}

			// Save the tid.
			if ( isset( $response->tid ) && ! empty( $response->tid ) ) {
				update_post_meta( $order->id, '_wc_cielo_transaction_tid', (string) $response->tid );
			}

			// Set the transaction URL.
			if ( isset( $response->{'url-autenticacao'} ) && ! empty( $response->{'url-autenticacao'} ) ) {
				$payment_url = (string) $response->{'url-autenticacao'};
			}
		}

		if ( $valid ) {
			return array(
				'result'   => 'success',
				'redirect' => $payment_url
			);
		} else {
			return array(
				'result'   => 'fail',
				'redirect' => ''
			);
		}
	}

}
