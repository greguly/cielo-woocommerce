<?php
/**
 * WC Cielo Banking Ticket Gateway Class.
 *
 * Built the Cielo Banking Ticket methods.
 */
class WC_Cielo_Banking_Ticket_Gateway extends WC_Cielo_Helper {

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
		$this->id           = 'cielo_banking_ticket';
		$this->icon         = apply_filters( 'wc_cielo_banking_ticket_icon', '' );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo - Banking Ticket', 'cielo-woocommerce' );
		$this->supports     = array( 'products', 'refunds' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title                     = $this->get_option( 'title' );
		$this->description               = $this->get_option( 'description' );
		$this->invoice_prefix            = $this->get_option( 'invoice_prefix' );
		$this->reduce_stock_on_order_gen = $this->get_option( 'reduce_stock_on_order_gen' );
		$this->store_contract            = $this->get_option( 'store_contract' );
		$this->environment               = $this->get_option( 'environment' );
		$this->number                    = $this->get_option( 'number' );
		$this->key                       = $this->get_option( 'key' );
		$this->methods                   = $this->get_option( 'methods' );
		$this->default_instruction       = $this->get_option( 'default_instruction' );
		$this->design                    = $this->get_option( 'design' );
		$this->debug                     = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = $this->get_logger();
		}

		// Set the API.
		$this->api = new WC_Cielo_API( $this );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_cielo_ticket_gateway', array( $this, 'check_return' ) );
		add_action( 'woocommerce_' . $this->id . '_return', array( $this, 'return_handler' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ), 999 );

		// Filters.
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'order_items_payment_details' ), 10, 2 );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'selected_api' => array(
				'title'       => __('Only on API 3.0', 'cielo-woocommerce' ),
				'type'        => 'title',
                'description' => __( 'Option only available on Cielo WebService 3.0', 'cielo-woocommerce' ),
			),
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'cielo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Cielo Banking Ticket', 'cielo-woocommerce' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Banking Ticket', 'cielo-woocommerce' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'cielo-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay using method Banking Ticket', 'cielo-woocommerce' ),
			),
			'invoice_prefix' => array(
				'title' => __( 'Store Identificator', 'cielo-woocommerce' ),
				'type' => 'text',
				'description' =>
					__( 'Please, inform a prefix to your store.', 'cielo-woocommerce' )
					. ' ' .
					__( 'If you use your Cielo account on multiple stores, you should make sure that this prefix is unique as Cielo will not allow orders with same identificators.', 'cielo-woocommerce' ),
				'default' => 'WC-'
			),
			'reduce_stock_on_order_gen' => array(
				'title' => __( 'Stock Reduce', 'cielo-woocommerce' ),
				'type' => 'checkbox',
				'label' =>
					__( 'Reduce Stock in Order Generation', 'cielo-woocommerce' ),
				'default' => 'no',
				'description' => __( 'Enable this to reduce the stock on order creation. Disable this to reduce <strong>after</strong> the payment approval.', 'cielo-woocommerce' )
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
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Select the environment type (test or production).', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'test',
				'options'     => array(
					'test'       => __( 'Test', 'cielo-woocommerce' ),
					'production' => __( 'Production', 'cielo-woocommerce' ),
				),
			),
			'merchant_id' => array(
				'title'       => __( 'Merchant ID', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Store merchant id number with Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'merchant_key' => array(
				'title'       => __( 'Merchant Key', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Store merchant key assigned by Cielo.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'default_instruction' => array(
				'title'       => __( 'Instruction', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This message is send with ticket instruction payment.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Accept only on until the date expiration, after expiration date 1% interest per day', 'cielo-woocommerce' ),
			),
			'design_options' => array(
				'title'       => __( 'Design', 'cielo-woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'design' => array(
				'title'   => __( 'Payment Form Design', 'cielo-woocommerce' ),
				'type'    => 'select',
				'class'       => 'wc-enhanced-select',
				'default' => 'default',
				'options' => array(
					'default' => __( 'Default', 'cielo-woocommerce' ),
					'icons'   => __( 'With ticket icons', 'cielo-woocommerce' ),
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
		woocommerce_get_template(
			'banking-ticket/' . $model . '-payment-form.php',
			array(
				'methods'        => $this->get_available_methods_options(),
				//'discount'       => $this->debit_discount,
				//'discount_total' => $this->get_debit_discount( $order_total ),
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

		if ( 'icons' == $this->design ) {
			wp_enqueue_style( 'wc-cielo-checkout-icons' );
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
//		$payment_url = '';
//		$card_number = isset( $_POST['cielo_debit_number'] ) ? sanitize_text_field( $_POST['cielo_debit_number'] ) : '';
//		$card_brand  = $this->api->get_card_brand( $card_number );
//
//		// Validate credit card brand.
//		if ( 'mastercard' === $card_brand ) {
//			$_card_brand = 'maestro';
//		} else if ( 'visa' === $card_brand ) {
//			$_card_brand = 'visaelectron';
//		} else {
//			$_card_brand = $card_brand;
//		}
//		$valid = $this->validate_credit_brand( $_card_brand );
//
//		// Test the card fields.
//		if ( $valid ) {
//			$valid = $this->validate_card_fields( $_POST );
//		}
//
//		if ( $valid ) {
//			$card_brand = ( 'maestro' === $card_brand ) ? 'mastercard' : $card_brand;
//			$card_data  = array(
//				'name_on_card'    => $_POST['cielo_debit_holder_name'],
//				'card_number'     => $_POST['cielo_debit_number'],
//				'card_expiration' => $_POST['cielo_debit_expiry'],
//				'card_cvv'        => $_POST['cielo_debit_cvc'],
//			);
//
//			$response = $this->api->do_transaction( $order, $order->id . '-' . time(), $card_brand, 0, $card_data, $this->id );
//
//			// Set the error alert.
//			if ( ! empty( $response->mensagem ) ) {
//				$this->add_error( (string) $response->mensagem );
//				$valid = false;
//			}
//
//			// Save the tid.
//			if ( ! empty( $response->tid ) ) {
//				update_post_meta( $order->id, '_transaction_id', (string) $response->tid );
//			}
//
//			// Set the transaction URL.
//			if ( ! empty( $response->{'url-autenticacao'} ) ) {
//				$payment_url = (string) $response->{'url-autenticacao'};
//			} else {
//				$payment_url = str_replace( '&amp;', '&', urldecode( $this->get_api_return_url( $order ) ) );
//			}
//
//			// Save payment data.
//			update_post_meta( $order->id, '_wc_cielo_card_brand', $card_brand );
//		}
//
//		if ( $valid && $payment_url ) {
//			return array(
//				'result'   => 'success',
//				'redirect' => $payment_url,
//			);
//		} else {
//			return array(
//				'result'   => 'fail',
//				'redirect' => '',
//			);
//		}
	}

	/**
	 * Process buy page cielo payment.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function process_buypage_cielo_payment( $order ) {
//		$payment_url = '';
//		$card_brand  = isset( $_POST['cielo_debit_card'] ) ? sanitize_text_field( $_POST['cielo_debit_card'] ) : '';
//
//		// Validate credit card brand.
//		$valid = $this->validate_credit_brand( $card_brand );
//
//		if ( $valid ) {
//			$card_brand = ( 'visaelectron' === $card_brand ) ? 'visa' : 'mastercard';
//			$response   = $this->api->do_transaction( $order, $order->id . '-' . time(), $card_brand, 0, array(), true );
//
//			// Set the error alert.
//			if ( ! empty( $response->mensagem ) ) {
//				$this->add_error( (string) $response->mensagem );
//				$valid = false;
//			}
//
//			// Save the tid.
//			if ( ! empty( $response->tid ) ) {
//				update_post_meta( $order->id, '_transaction_id', (string) $response->tid );
//			}
//
//			// Set the transaction URL.
//			if ( ! empty( $response->{'url-autenticacao'} ) ) {
//				$payment_url = (string) $response->{'url-autenticacao'};
//			}
//
//			update_post_meta( $order->id, '_wc_cielo_card_brand', $card_brand );
//		}
//
//		if ( $valid && $payment_url ) {
//			return array(
//				'result'   => 'success',
//				'redirect' => $payment_url,
//			);
//		} else {
//			return array(
//				'result'   => 'fail',
//				'redirect' => '',
//			);
//		}
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
//			$card_brand   = get_post_meta( $order->id, '_wc_cielo_card_brand', true );
//			$card_brand   = $this->get_payment_method_name( $card_brand );

//			$items['payment_method']['value'] .= '<br />';
//			$items['payment_method']['value'] .= '<small>';
//			$items['payment_method']['value'] .= esc_attr( $card_brand );

//			if ( 0 < $this->debit_discount ) {
//				$discount_total = $this->get_debit_discount( (float) $order->get_total() );
//
//				$items['payment_method']['value'] .= ' ';
//				$items['payment_method']['value'] .= sprintf( __( 'with discount of %s. Order Total: %s.', 'cielo-woocommerce' ), $this->debit_discount . '%', sanitize_text_field( woocommerce_price( $discount_total ) ) );
//			}

//			$items['payment_method']['value'] .= '</small>';
		}

		return $items;
	}
}
