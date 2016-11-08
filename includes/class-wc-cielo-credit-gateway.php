<?php
/**
 * WC Cielo Credit Gateway Class.
 *
 * Built the Cielo Credit methods.
 */
class WC_Cielo_Credit_Gateway extends WC_Cielo_Helper {

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
		$this->id           = 'cielo_credit';
		$this->icon         = apply_filters( 'wc_cielo_credit_icon', '' );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo - Credit Card', 'cielo-woocommerce' );
		$this->supports     = array( 'products', 'refunds' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->store_contract       = $this->get_option( 'store_contract' );
		$this->environment          = $this->get_option( 'environment' );
		$this->number               = $this->get_option( 'number' );
		$this->key                  = $this->get_option( 'key' );
		$this->methods              = $this->get_option( 'methods' );
		$this->authorization        = $this->get_option( 'authorization' );
		$this->smallest_installment = $this->get_option( 'smallest_installment' );
		$this->interest_rate        = $this->get_option( 'interest_rate' );
		$this->installments         = $this->get_option( 'installments' );
		$this->interest             = $this->get_option( 'interest' );
		$this->installment_type     = $this->get_option( 'installment_type' );
		$this->design               = $this->get_option( 'design' );
		$this->debug                = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = $this->get_logger();
		}

		// Set the API.
		$this->api = new WC_Cielo_API( $this );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_cielo_credit_gateway', array( $this, 'check_return' ) );
		add_action( 'woocommerce_' . $this->id . '_return', array( $this, 'return_handler' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ) );

		// Filters.
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'order_items_payment_details' ), 10, 2 );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'cielo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Cielo Credit Card', 'cielo-woocommerce' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Credit Card', 'cielo-woocommerce' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'cielo-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay using the secure method of Cielo', 'cielo-woocommerce' ),
			),
			'store_contract' => array(
				'title'       => __( 'Store Solution', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the store contract method with cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => 'webservice',
				'options'     => array(
					'webservice'    => __( 'Webservice Solution', 'cielo-woocommerce' ),
					'buypage_cielo' => __( 'BuyPage Cielo', 'cielo-woocommerce' ),
				),
			),
			'environment' => array(
				'title'       => __( 'Environment', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the environment type (test or production).', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => 'test',
				'options'     => array(
					'test'       => __( 'Test', 'cielo-woocommerce' ),
					'production' => __( 'Production', 'cielo-woocommerce' ),
				),
			),
			'number' => array(
				'title'       => __( 'Affiliation Number', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Store affiliation number with Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'key' => array(
				'title'       => __( 'Affiliation Key', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Store access key assigned by Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'methods' => array(
				'title'       => __( 'Accepted Card Brands', 'cielo-woocommerce' ),
				'type'        => 'multiselect',
				'description' => __( 'Select the card brands that will be accepted as payment. Press the Ctrl key to select more than one brand.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => array( 'visa', 'mastercard' ),
				'options'     => array(
					'visa'       => __( 'Visa', 'cielo-woocommerce' ),
					'mastercard' => __( 'MasterCard', 'cielo-woocommerce' ),
					'diners'     => __( 'Diners', 'cielo-woocommerce' ),
					'discover'   => __( 'Discover', 'cielo-woocommerce' ),
					'elo'        => __( 'Elo', 'cielo-woocommerce' ),
					'amex'       => __( 'American Express', 'cielo-woocommerce' ),
					'jcb'        => __( 'JCB', 'cielo-woocommerce' ),
					'aura'       => __( 'Aura', 'cielo-woocommerce' ),
				),
			),
			'authorization' => array(
				'title'       => __( 'Automatic Authorization (MasterCard and Visa only)', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select the authorization type.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => '2',
				'options'     => array(
					'3' => __( 'Direct authorization', 'cielo-woocommerce' ),
					'2' => __( 'Allow authorization for authenticated transaction and non-authenticated', 'cielo-woocommerce' ),
					'1' => __( 'Authorization transaction only if is authenticated', 'cielo-woocommerce' ),
					'0' => __( 'Only authenticate the transaction', 'cielo-woocommerce' ),
				),
			),
			'smallest_installment' => array(
				'title'       => __( 'Smallest Installment', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Smallest value of each installment, cannot be less than 5.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '5',
			),
			'installments' => array(
				'title'       => __( 'Installment Within', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Maximum number of installments for orders in your store.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
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
					'12' => '12x',
				),
			),
			'installment_type' => array(
				'title'        => __( 'Installment Type', 'cielo-woocommerce' ),
				'type'         => 'select',
				'description'  => __( 'Client adds interest installments on the order total.', 'cielo-woocommerce' ),
				'desc_tip'     => true,
				'class'        => 'wc-enhanced-select',
				'default'      => 'client',
				'options'      => array(
					'client' => __( 'Client', 'cielo-woocommerce' ),
					'store'  => __( 'Store', 'cielo-woocommerce' ),
				),
			),
			'interest_rate' => array(
				'title'       => __( 'Interest Rate (%)', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Percentage of interest that will be charged to the customer in the installment where there is interest rate to be charged.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '2',
			),
			'interest' => array(
				'title'       => __( 'Charge Interest Since', 'cielo-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Indicate from which installment should be charged interest.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
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
					'12' => '12x',
				),
			),
			'design_options' => array(
				'title'       => __( 'Design', 'cielo-woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'design' => array(
				'title'   => __( 'Payment Form Design', 'cielo-woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'default',
				'options' => array(
					'default' => __( 'Default', 'cielo-woocommerce' ),
					'icons'   => __( 'With card icons', 'cielo-woocommerce' ),
				),
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'cielo-woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'cielo-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'cielo-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Cielo events, such as API requests, inside %s', 'cielo-woocommerce' ), $this->get_log_file_path() ),
			),
		);
	}

	/**
	 * Get Checkout form field.
	 *
	 * @param string $model
	 * @param float  $order_total
	 */
	protected function get_checkout_form( $model = 'default', $order_total = 0 ) {
		$installments_type = ( 'icons' == $model ) ? 'radio' : 'select';

		woocommerce_get_template(
			'credit-card/' . $model . '-payment-form.php',
			array(
				'methods'      => $this->get_available_methods_options(),
				'installments' => $this->get_installments_html( $order_total, $installments_type ),
			),
			'woocommerce/cielo/',
			WC_Cielo::get_templates_path()
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

		if ( 'webservice' == $this->store_contract ) {
			wp_enqueue_style( 'wc-cielo-checkout-webservice' );
			wp_enqueue_script( 'wc-cielo-credit-checkout-webservice', plugins_url( 'assets/js/credit-card/checkout-webservice' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'wc-credit-card-form' ), WC_Cielo::VERSION, true );
		} else {
			if ( 'icons' == $this->design ) {
				wp_enqueue_style( 'wc-cielo-checkout-icons' );
				wp_enqueue_script( 'wc-cielo-credit-checkout-icons', plugins_url( 'assets/js/credit-card/checkout-icons' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );
			} else {
				wp_enqueue_script( 'wc-cielo-credit-checkout-default', plugins_url( 'assets/js/credit-card/checkout-default' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );
			}
		}
	}

	/**
	 * Process webservice payment.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function process_webservice_payment( $order ) {
		$payment_url = '';
		$card_number = isset( $_POST['cielo_credit_number'] ) ? sanitize_text_field( $_POST['cielo_credit_number'] ) : '';
		$card_brand  = $this->api->get_card_brand( $card_number );

		// Validate credit card brand.
		$valid = $this->validate_credit_brand( $card_brand );

		// Test the card fields.
		if ( $valid ) {
			$valid = $this->validate_card_fields( $_POST );
		}

		// Test the installments.
		if ( $valid ) {
			$valid = $this->validate_installments( $_POST, $order->order_total );
		}

		if ( $valid ) {
			$installments = isset( $_POST['cielo_credit_installments'] ) ? absint( $_POST['cielo_credit_installments'] ) : 1;
			$card_data    = array(
				'name_on_card'    => $_POST['cielo_credit_holder_name'],
				'card_number'     => $_POST['cielo_credit_number'],
				'card_expiration' => $_POST['cielo_credit_expiry'],
				'card_cvv'        => $_POST['cielo_credit_cvc'],
			);

			$response = $this->api->do_transaction( $order, $order->id . '-' . time(), $card_brand, $installments, $card_data );

			// Set the error alert.
			if ( ! empty( $response->mensagem ) ) {
				$this->add_error( (string) $response->mensagem );
				$valid = false;
			}

			// Save the tid.
			if ( ! empty( $response->tid ) ) {
				update_post_meta( $order->id, '_transaction_id', (string) $response->tid );
			}

			// Set the transaction URL.
			if ( ! empty( $response->{'url-autenticacao'} ) ) {
				$payment_url = (string) $response->{'url-autenticacao'};
			} else {
				$payment_url = str_replace( '&amp;', '&', urldecode( $this->get_api_return_url( $order ) ) );
			}

			// Save payment data.
			update_post_meta( $order->id, '_wc_cielo_card_brand', $card_brand );
			update_post_meta( $order->id, '_wc_cielo_installments', $installments );
		}

		if ( $valid && $payment_url ) {
			return array(
				'result'   => 'success',
				'redirect' => $payment_url,
			);
		} else {
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
	}

	/**
	 * Process buy page cielo payment.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function process_buypage_cielo_payment( $order ) {
		$payment_url = '';
		$card_brand  = isset( $_POST['cielo_credit_card'] ) ? sanitize_text_field( $_POST['cielo_credit_card'] ) : '';

		// Validate credit card brand.
		$valid = $this->validate_credit_brand( $card_brand );

		// Test the installments.
		if ( $valid ) {
			$valid = $this->validate_installments( $_POST, $order->order_total );
		}

		if ( $valid ) {
			$installments = isset( $_POST['cielo_credit_installments'] ) ? absint( $_POST['cielo_credit_installments'] ) : 1;
			$response     = $this->api->do_transaction( $order, $order->id . '-' . time(), $card_brand, $installments );

			// Set the error alert.
			if ( ! empty( $response->mensagem ) ) {
				$this->add_error( (string) $response->mensagem );
				$valid = false;
			}

			// Save the tid.
			if ( ! empty( $response->tid ) ) {
				update_post_meta( $order->id, '_transaction_id', (string) $response->tid );
			}

			// Set the transaction URL.
			if ( ! empty( $response->{'url-autenticacao'} ) ) {
				$payment_url = (string) $response->{'url-autenticacao'};
			}

			update_post_meta( $order->id, '_wc_cielo_card_brand', $card_brand );
			update_post_meta( $order->id, '_wc_cielo_installments', $installments );
		}

		if ( $valid && $payment_url ) {
			return array(
				'result'   => 'success',
				'redirect' => $payment_url,
			);
		} else {
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
	}

	/**
	 * Payment details.
	 *
	 * @param  array    $items
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	public function order_items_payment_details( $items, $order ) {
		if ( $this->id === $order->payment_method ) {
			$card_brand   = get_post_meta( $order->id, '_wc_cielo_card_brand', true );
			$card_brand   = $this->get_payment_method_name( $card_brand );
			$installments = get_post_meta( $order->id, '_wc_cielo_installments', true );

			$items['payment_method']['value'] .= '<br />';
			$items['payment_method']['value'] .= '<small>';
			$items['payment_method']['value'] .= sprintf( __( '%s in %s.', 'cielo-woocommerce' ), esc_attr( $card_brand ), $this->get_installment_text( $installments, (float) $order->get_total() ) );
			$items['payment_method']['value'] .= '</small>';
		}

		return $items;
	}
}
