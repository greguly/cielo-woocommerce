<?php
/**
 * WC Cielo Direct Debit Gateway Class.
 *
 * Built the Cielo Direct Debit methods.
 */
class WC_Cielo_Direct_Debit_Gateway extends WC_Cielo_Helper {

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
		$this->id           = 'cielo_direct_debit';
		$this->icon         = apply_filters( 'wc_cielo_direct_debit_icon', plugins_url( 'assets/images/directdebit.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo - Direct Debit', 'cielo-woocommerce' );
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
		$this->methods          = $this->get_option( 'methods' );
		$this->design           = $this->get_option( 'design' );
		$this->debug            = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			$this->log = $this->get_logger();
		}

		// Set the API.
		$this->api = new WC_Cielo_API( $this );

		// Actions.
		add_action( 'woocommerce_api_wc_cielo_direct_debit_gateway', array( $this, 'check_return' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
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
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'cielo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Cielo Direct Debit', 'cielo-woocommerce' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'cielo-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Direct Debit', 'cielo-woocommerce' ),
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
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Select the environment type (test or production).', 'cielo-woocommerce' ),
				'desc_tip'    => true,
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
				'title'   => __( 'Direct Debit Methods', 'cielo-woocommerce' ),
				'type'    => 'multiselect',
                'description' => __( 'Select the method\'s that will be accepted as payment. Press the Ctrl key to select more than one method.', 'cielo-woocommerce' ),
                'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default' => array( 'bancodobrasil', 'bradesco' ),
				'options' => array(
					'bancodobrasil' => __( 'Banco do Brasil', 'cielo-woocommerce' ),
					'bradesco'      => __( 'Bradesco', 'cielo-woocommerce' ),
				),
			),
            'integration' => array(
                'title'       => __( 'Integration Settings', 'cielo-woocommerce' ),
                'type'        => 'title',
                'description' => sprintf( __( 'For the integration work you need to set the following urls in the %s:', 'cielo-woocommerce' ), '<br /><br /><strong>' . __( 'Notification URL:', 'cielo-woocommerce' ) . '</strong> <code>' . esc_url( WC()->api_request_url( 'WC_Checkout_Cielo_Gateway' ) ) . '</code><br /><strong>' . __( 'Status Change URL:', 'cielo-woocommerce' ) . '</strong> <code>' . esc_url( WC()->api_request_url( 'WC_Checkout_Cielo_Gateway' ) ) . '</code>'),
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
			'direct-debit/' . $model . '-payment-form.php',
			array(
				'methods'        => $this->get_available_methods_options(),
//				'discount'       => $this->debit_discount,
//				'discount_total' => $this->get_debit_discount( $order_total ),
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
			$gateway_data  = array(
				'name_of_bank' => $_POST['cielo_direct_debit'],
			);

			$response = $this->api->do_transaction( $order, $order->id . '-' . time(), '', 1, $gateway_data, $this->id );

			$process = $this->api->api->process_webservice_payment(true, $order, $response);
			$valid = $process['valid'];
			$payment_url = $process['payment_url'];

			// Save payment data.
			update_post_meta( $order->id, '_wc_cielo_direct_debit_brand', $gateway_data['name_of_bank'] );

//		}

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
		$card_brand  = isset( $_POST['cielo_debit_card'] ) ? sanitize_text_field( $_POST['cielo_debit_card'] ) : '';

		// Validate credit card brand.
		$valid = $this->validate_credit_brand( $card_brand );

		if ( $valid ) {
			$card_brand = ( 'visaelectron' === $card_brand ) ? 'visa' : 'mastercard';
			$response   = $this->api->do_transaction( $order, $order->id . '-' . time(), $card_brand, 0, array(), true );

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

			update_post_meta( $order->id, '_wc_cielo_direct_debit_brand', $card_brand );
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
			$direct_debit_brand   = get_post_meta( $order->id, '_wc_cielo_direct_debit_brand', true );
			//$direct_debit_brand = $this->get_payment_method_name( $direct_debit_brand );

			$items['payment_method']['value'] .= '<br />';
			$items['payment_method']['value'] .= '<small>';
			$items['payment_method']['value'] .= esc_attr( $direct_debit_brand );

//			if ( 0 < $this->debit_discount ) {
//				$discount_total = $this->get_debit_discount( (float) $order->get_total() );
//
//				$items['payment_method']['value'] .= ' ';
//				$items['payment_method']['value'] .= sprintf( __( 'with discount of %s. Order Total: %s.', 'cielo-woocommerce' ), $this->debit_discount . '%', sanitize_text_field( woocommerce_price( $discount_total ) ) );
//			}

			$items['payment_method']['value'] .= '</small>';
		}

		return $items;
	}
}
