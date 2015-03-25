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
	public $api = null;

	/**
	 * Cielo Helpers.
	 *
	 * @var WC_Cielo_Helper
	 */
	public $helper = null;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id           = 'cielo';
		$this->icon         = apply_filters( 'wc_cielo_icon', plugins_url( 'assets/images/cielo.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo', 'cielo-woocommerce' );
		$this->supports     = array( 'products', 'refunds' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->store_contract		= $this->get_option( 'store_contract', 'buypage_cielo' );
		$this->environment          = $this->get_option( 'environment' );
		$this->number               = $this->get_option( 'number' );
		$this->key                  = $this->get_option( 'key' );
		$this->methods              = $this->get_option( 'methods' );
		$this->debit_methods        = $this->get_option( 'debit_methods', 'visa' );
		$this->authorization        = $this->get_option( 'authorization' );
		$this->smallest_installment = $this->get_option( 'smallest_installment' );
		$this->interest_rate        = $this->get_option( 'interest_rate' );
		$this->debit_discount       = $this->get_option( 'debit_discount' );
		$this->installments         = $this->get_option( 'installments' );
		$this->interest             = $this->get_option( 'interest' );
		$this->installment_type     = $this->get_option( 'installment_type' );
		$this->design               = $this->get_option( 'design' );
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

		// Set the helper.
		$this->helper = new WC_Cielo_Helper( $this );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_cielo_gateway', array( $this, 'check_return' ) );
		add_action( 'woocommerce_cielo_return', array( $this, 'return_handler' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		return ( 'BRL' == get_woocommerce_currency() );
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
		$available = parent::is_available() && 'yes' == $this->get_option( 'enabled' ) && $this->check_environment() && $this->using_supported_currency();

		return $available;
	}

	/**
	 * Check the environment.
	 *
	 * @return bool
	 */
	public function check_environment() {
		if ( 'test' == $this->environment ) {
			return true;
		}

		// For production.
		return ( ! empty( $this->methods ) && ! empty( $this->number ) && ! empty( $this->key ) );
	}

	/**
	 * Get log file path
	 *
	 * @return string
	 */
	public function get_log_file_path() {
		if ( function_exists( 'wc_get_log_file_path' ) ) {
			return '<code><a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '" title="' . __( 'View log file', 'cielo-woocommerce' ) . '">' . wc_get_log_file_path( $this->id ) . '</a></code>';
		} else {
			return '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>';
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
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
			'store_contract' => array(
				'title'       => __( 'Store Solution', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the store contract method with cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'buypage_cielo',
				'options'     => array(
					'buypage_cielo' => __( 'BuyPage Cielo', 'cielo-woocommerce' ),
					//'checkout_cielo' => __( 'Checkout Cielo', 'cielo-woocommerce' ),
					'webservice'   => __( 'Webservice Solution', 'cielo-woocommerce' )
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
			'merchant_id' => array(
				'title'       => __( 'Merchant ID', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Merchant ID store number with Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'antifraud' => array(
				'title'       => __( 'Anti-Fraud Analysis', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Enable anti-fraud option to analyse the transactions on cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'true',
				'options'     => array(
					'true'  => __( 'Enabled', 'cielo-woocommerce' ),
					'false'  => __( 'Disabled', 'cielo-woocommerce' ),
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
				'title'       => __( 'Accepted Card Brands', 'cielo-woocommerce' ),
				'type'        => 'multiselect',
				'description' => __( 'Select the card brands that will be accepted as payment. Press the Ctrl key to select more than one brand.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => array( 'visa', 'mastercard' ),
				'options'     => WC_Cielo_Helper::get_payment_methods()
			),
			'debit_methods' => array(
				'title'       => __( 'Accepted Debit Cards', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the debit card that will be accepted as payment.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'visa',
				'options'     => array(
					'none'       => __( 'None', 'cielo-woocommerce' ),
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
					'3' => __( 'Direct authorization (does not work for debit)', 'cielo-woocommerce' ),
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
				'description' => sprintf( __( 'Log Cielo events, such as API requests, inside %s', 'cielo-woocommerce' ),  $this->get_log_file_path() )
			)
		);
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		include 'views/html-admin-page.php';
	}

	/**
	 * Checkout scripts.
	 */
	public function checkout_scripts() {
		if ( is_checkout() ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( 'webservice' == $this->store_contract ) {
				wp_enqueue_style( 'wc-cielo-checkout-webservice', plugins_url( 'assets/css/checkout-webservice' . $suffix . '.css', plugin_dir_path( __FILE__ ) ), array(), WC_Cielo::VERSION );
				wp_enqueue_script( 'wc-cielo-checkout-webservice', plugins_url( 'assets/js/checkout-webservice' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'wc-credit-card-form' ), WC_Cielo::VERSION, true );
				wp_localize_script(
					'wc-cielo-checkout-webservice',
					'wc_cielo_checkout_webservice_params',
					array(
						'available_brands' => $this->methods
					)
				);
			} else {
				if ( 'icons' == $this->design ) {
					wp_enqueue_style( 'wc-cielo-checkout-icons', plugins_url( 'assets/css/checkout-icons' . $suffix . '.css', plugin_dir_path( __FILE__ ) ), array(), WC_Cielo::VERSION );
					wp_enqueue_script( 'wc-cielo-checkout-icons', plugins_url( 'assets/js/checkout-icons' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );
				} else {
					wp_enqueue_script( 'wc-cielo-checkout-default', plugins_url( 'assets/js/checkout-default' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );
				}
			}
		}
	}

	/**
	 * Admin scripts.
	 *
	 * @param  string $hook Page slug.
	 */
	public function admin_scripts( $hook ) {
		if ( in_array( $hook, array( 'woocommerce_page_wc-settings', 'woocommerce_page_woocommerce_settings' ) ) && ( isset( $_GET['section'] ) && in_array( $_GET['section'], array( 'wc_cielo_gateway', 'WC_Cielo_Gateway' ) ) ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'wc-cielo-admin', plugins_url( 'assets/js/admin' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );
		}
	}

	/**
	 * Add error messages in checkout.
	 *
	 * @param  string $message Error message.
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

		$cart_total = 0;
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
		} else {
			$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		}

		// Gets order total from "pay for order" page.
		if ( 0 < $order_id ) {
			$order      = new WC_Order( $order_id );
			$cart_total = (float) $order->get_total();

		// Gets order total from cart/checkout.
		} elseif ( 0 < $woocommerce->cart->total ) {
			$cart_total = (float) $woocommerce->cart->total;
		}

		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		// Set the payment form type.
		if ( 'webservice' == $this->store_contract ) {
			wp_enqueue_script( 'wc-credit-card-form' );

			$model = 'webservice';
		} else {
			$model = ( 'icons' == $this->design ) ? 'icons' : 'default';
		}

		// Makes it possible to create custom templates.
		$path = apply_filters( 'wc_cielo_form_path', plugin_dir_path( __FILE__ ) . 'views/html-payment-form-' . $model . '.php', $model );

		if ( file_exists( $path ) ) {
			include_once( $path );
		}
	}

	/**
	 * Process the payment for webservice solution and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	protected function process_payment_webservice($order_id){

		$order        = new WC_Order( $order_id );
		$card         = isset( $_POST['cielo_card'] ) ? sanitize_text_field( $_POST['cielo_card'] ) : '';
		$installments = isset( $_POST['cielo_installments'] ) ? absint( $_POST['cielo_installments'] ) : '';
		$valid        = true;
		$payment_url  = '';

		$name_on_card    = isset( $_POST['cielo_holder_name'] ) ? sanitize_text_field( $_POST['cielo_holder_name'] ) : false;
		$card_number     = isset( $_POST['cielo_card_number'] ) ? sanitize_text_field( $_POST['cielo_card_number'] ) : false;
		$card_expiration = isset( $_POST['cielo_card_expiry'] ) ? sanitize_text_field( $_POST['cielo_card_expiry'] ) : false;
		$card_cvv        = isset( $_POST['cielo_card_cvc'] ) ? sanitize_text_field( $_POST['cielo_card_cvc'] ) : false;
		$card_webservice = array();

		// Validate the card brand.
		if ( ! in_array( $card, $this->methods ) ) {
			$this->add_error( sprintf( __( 'Select a valid card brand. The following cards are accepted: %s.', 'cielo-woocommerce' ), WC_Cielo_Helper::get_accepted_brands_list( $this->methods ) ) );
			$valid = false;
		}

		// Validate the installments field.
		if ( '' === $installments ) {
			$this->add_error( __( 'Please select a number of installments.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate card number was typed for the card
		if ( ! $card_number ) {
			$this->add_error( __( 'Please type the card number.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate name typed for the card
		if ( ! $name_on_card ) {
			$this->add_error( __( 'Please type the name of the card holder.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate the expiration date
		if ( ! $card_expiration ) {
			$this->add_error( __( 'Please type the card expiry date.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate the cvv for the card
		if ( ! $card_cvv ) {
			$this->add_error( __( 'Please type the cvv code for the card', 'cielo-woocommerce' ) );
			$valid = false;
		}

		$card_webservice = array(
			'name_on_card'    => $name_on_card,
			'card_expiration' => $card_expiration,
			'card_cvv'        => $card_cvv,
			'card_number'     => $card_number
		);


		// Validate if debit is available.
		if ( ! in_array( $card, WC_Cielo_Helper::get_debit_methods( $this->debit_methods ) ) && 0 === $installments ) {
			$this->add_error( sprintf( __( '%s does not accept payment by debit.', 'cielo-woocommerce' ), WC_Cielo_Helper::get_payment_method_name( $card ) ) );
			$valid = false;
		}

		if ( 0 != $installments ) {

			$interest_rate = WC_Cielo_Helper::get_valid_value( $this->interest_rate )/100;
			$financial_index = $interest_rate / (1- (1/pow((1+$interest_rate),$installments)));

			$installment_total    = $order->order_total  / $installments;
			if ( 'client' == $this->installment_type && $installments >= $this->interest ) {
				$interest_total = $installment_total * $financial_index;
				$installment_total = ( $installment_total < $interest_total ) ? $interest_total : $installment_total;

			}
			$smallest_value = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

			 if ( $installments > $this->installments || 1 != $installments && $installment_total < $smallest_value ) {
				$this->add_error( __( 'Invalid number of installments!', 'cielo-woocommerce' ) );
				$valid = false;
			}

		}

		if ( $valid ) {

			$response = $this->api->do_transaction( $order, $order->id . '-' . time(), $card, $installments, $card_webservice );

			// Set the error alert.
			if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
				$this->add_error( (string) $response->mensagem );
				$valid = false;
			}

			// Save the tid.
			if ( isset( $response->tid ) && ! empty( $response->tid ) ) {
				update_post_meta( $order->id, '_wc_cielo_transaction_tid', (string) $response->tid );

				// For WooCommerce 2.2 or later.
				update_post_meta( $order->id, '_transaction_id', (string) $response->tid );
			}

			$payment_url = str_replace( '&amp;', '&', urldecode( WC_Cielo_Helper::get_return_url( $order ) ) );

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

	/**
	 * Process the payment for buypage cielo solution and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	protected function process_payment_buypage_cielo($order_id){

		$order        = new WC_Order( $order_id );
		$card         = isset( $_POST['cielo_card'] ) ? sanitize_text_field( $_POST['cielo_card'] ) : '';
		$installments = isset( $_POST['cielo_installments'] ) ? absint( $_POST['cielo_installments'] ) : '';
		$valid        = true;
		$payment_url  = '';

		// Validate the card brand.
		if ( ! in_array( $card, $this->methods ) ) {
			$this->add_error( sprintf( __( 'Select a valid card brand. The following cards are accepted: %s.', 'cielo-woocommerce' ), WC_Cielo_Helper::get_accepted_brands_list( $this->methods ) ) );
			$valid = false;
		}

		// Validate the installments field.
		if ( '' === $installments ) {
			$this->add_error( __( 'Please select a number of installments.', 'cielo-woocommerce' ) );
			$valid = false;
		}

		// Validate if debit is available.
		if ( ! in_array( $card, WC_Cielo_Helper::get_debit_methods( $this->debit_methods ) ) && 0 === $installments ) {
			$this->add_error( sprintf( __( '%s does not accept payment by debit.', 'cielo-woocommerce' ), WC_Cielo_Helper::get_payment_method_name( $card ) ) );
			$valid = false;
		}

		if ( 0 != $installments ) {
			// Validate the installments amount.
			$interest_rate = WC_Cielo_Helper::get_valid_value( $this->interest_rate )/100;
			$financial_index = $interest_rate / (1- (1/pow((1+$interest_rate),$installments)));

			$installment_total    = $order->order_total  / $installments;
			if ( 'client' == $this->installment_type && $installments >= $this->interest ) {
				$interest_total = $installment_total * $financial_index;
				$installment_total = ( $installment_total < $interest_total ) ? $interest_total : $installment_total;

			}
			$smallest_value = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

			 if ( $installments > $this->installments || 1 != $installments && $installment_total < $smallest_value ) {
				$this->add_error( __( 'Invalid number of installments!', 'cielo-woocommerce' ) );
				$valid = false;
			}
		}

		if ( $valid ) {

			$response = $this->api->do_transaction( $order, $order->id . '-' . time(), $card, $installments );

			// Set the error alert.
			if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
				$this->add_error( (string) $response->mensagem );
				$valid = false;
			}

			// Save the tid.
			if ( isset( $response->tid ) && ! empty( $response->tid ) ) {
				update_post_meta( $order->id, '_wc_cielo_transaction_tid', (string) $response->tid );

				// For WooCommerce 2.2 or later.
				update_post_meta( $order->id, '_transaction_id', (string) $response->tid );
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

	/**
	 * Process the payment for checkout solution and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	protected function process_payment_checkout_cielo($order_id){
		//TODO process payment function for checkout cielo
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) {

		$payment_function = 'process_payment_'.$this->store_contract;
		return call_user_func($payment_function,$order_id);
	}

	/**
	 * Check return.
	 */
	public function check_return() {
		@ob_clean();

		if ( isset( $_GET['key'] ) && isset( $_GET['order'] ) ) {
			header( 'HTTP/1.1 200 OK' );

			$order_id = absint( $_GET['order'] );
			$order    = new WC_Order( $order_id );

			if ( $order->order_key == $_GET['key'] ) {
				do_action( 'woocommerce_cielo_return', $order );
			}
		}

		wp_die( __( 'Invalid request', 'cielo-woocommerce' ) );
	}

	/**
	 * Return handler.
	 *
	 * @param  WC_Order $order Order data.
	 */
	public function return_handler( $order ) {
		global $woocommerce;

		$tid = get_post_meta( $order->id, '_wc_cielo_transaction_tid', true );

		if ( '' != $tid ) {
			$response = $this->api->get_transaction_data( $order, $tid, $order->id . '-' . time() );

			// Set the error alert.
			if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Cielo payment error: ' . print_r( $response->mensagem, true ) );
				}

				$this->add_error( (string) $response->mensagem );
			}

			// Update the order status.
			$status = ( isset( $response->status ) && ! empty( $response->status ) ) ? intval( $response->status ) : -1;
			$order_note = "\n";

			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, 'Cielo payment status: ' . $status );
			}

			// For backward compatibility!
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.12', '<=' ) ) {
				$order_note = "\n" . 'TID: ' . $tid . '.';
			}

			if ( isset( $response->{'forma-pagamento'} ) ) {
				$payment_method = $response->{'forma-pagamento'};

				$order_note .= "\n";
				$order_note .= __( 'Paid with', 'cielo-woocommerce' );
				$order_note .= ' ';
				$order_note .= WC_Cielo_Helper::get_payment_method_name( (string) $payment_method->bandeira );
				$order_note .= ' ';

				if ( 'A' == $payment_method->produto ) {
					$order_note .=  __( 'debit', 'cielo-woocommerce' );
				} elseif ( '1' == $payment_method->produto ) {
					$order_note .= __( 'credit at sight', 'cielo-woocommerce' );
				} else {
					$order_note .= sprintf( __( 'credit %dx', 'cielo-woocommerce' ), $payment_method->parcelas );
				}

				$order_note .= '.';
			}
			$this->helper->process_order_status( $order, $status, $order_note );

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
				$return_url = $this->get_return_url( $order );
			} else {
				$return_url = add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) );
			}

			// Order cancelled.
			if ( 9 == $status ) {
				$message = __( 'Order canceled successfully.', 'cielo-woocommerce' );
				if ( function_exists( 'wc_add_notice' ) ) {
					wc_add_notice( $message );
				} else {
					$woocommerce->add_message( $message);
				}

				if ( function_exists( 'wc_get_page_id' ) ) {
					$return_url = get_permalink( wc_get_page_id( 'shop' ) );
				} else {
					$return_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
				}
			}

			wp_redirect( $return_url );
			exit;
		} else {
			if ( function_exists( 'wc_get_page_id' ) ) {
				$cart_url = get_permalink( wc_get_page_id( 'cart' ) );
			} else {
				$cart_url = get_permalink( woocommerce_get_page_id( 'cart' ) );
			}

			wp_redirect( $cart_url );
			exit;
		}
	}

	/**
	 * Thank you page message.
	 *
	 * @return string
	 */
	public function thankyou_page( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$order_url = $order->get_view_order_url();
		} else {
			$order_url = add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'view_order' ) ) );
		}

		if ( $order->status == 'processing' || $order->status == 'completed' ) {
			echo '<div class="woocommerce-message"><a href="' . $order_url . '" class="button" style="display: block !important; visibility: visible !important;">' . __( 'View order details', 'cielo-woocommerce' ) . '</a>' . sprintf( __( 'Your payment worth %s was received successfully.', 'cielo-woocommerce' ), woocommerce_price( $order->order_total ) ) . '<br />' . __( 'The authorization code was generated.', 'cielo-woocommerce' ) . '</div>';
		} else {
			echo '<div class="woocommerce-info">' . sprintf( __( 'For more information or questions regarding your order, go to the %s.', 'cielo-woocommerce' ), '<a href="' . $order_url . '">' . __( 'order details page', 'cielo-woocommerce' ) . '</a>' ) . '</div>';
		}
	}

	/**
	 * Process a refund in WooCommerce 2.2 or later.
	 *
	 * @param  int    $order_id
	 * @param  float  $amount
	 * @param  string $reason
	 *
	 * @return bool|wp_error True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return $this->helper->process_refund( $order_id, $amount, $reason );
	}
}
