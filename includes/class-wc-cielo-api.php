<?php
/**
 * WC Cielo API Class.
 */
class WC_Cielo_API {

	/**
	 * API version.
	 */
	const VERSION = '1.4.0';

	/**
	 * Currency.
	 */
	const CURRENCY = '986';

	/**
	 * Gateway class.
	 *
	 * @var WC_Cielo_Gateway
	 */
	protected $gateway;

	/**
	 * Charset.
	 *
	 * @var string
	 */
	protected $charset = 'ISO-8859-1';

	/**
	 * Test Environment URL.
	 *
	 * @var string
	 */
	protected $test_url = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do';

	/**
	 * Production Environment URL.
	 *
	 * @var string
	 */
	protected $production_url = 'https://ecommerce.cielo.com.br/servicos/ecommwsec.do';

	/**
	 * Test Store Number.
	 *
	 * @var string
	 */
	protected $test_store_number = '1006993069';

	/**
	 * Test Store Key.
	 *
	 * @var string
	 */
	protected $test_store_key = '25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3';

	/**
	 * Test Cielo Number.
	 *
	 * @var string
	 */
	protected $test_cielo_number = '1001734898';

	/**
	 * Test Cielo Key.
	 *
	 * @var string
	 */
	protected $test_cielo_key = 'e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832';

	/**
	 * Constructor.
	 *
	 * @param WC_Cielo_Gateway $gateway
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
		$this->charset = get_bloginfo( 'charset' );
	}

	/**
	 * Get payment methods.
	 *
	 * @return array
	 */
	public static function get_payment_methods() {
		return array(
			'visa'       => __( 'Visa', 'cielo-woocommerce' ),
			'mastercard' => __( 'MasterCard', 'cielo-woocommerce' ),
			'diners'     => __( 'Diners', 'cielo-woocommerce' ),
			'discover'   => __( 'Discover', 'cielo-woocommerce' ),
			'elo'        => __( 'Elo', 'cielo-woocommerce' ),
			'amex'       => __( 'American Express', 'cielo-woocommerce' )
		);
	}

	/**
	 * Get payment method name.
	 *
	 * @param  string $slug Payment method slug.
	 *
	 * @return string       Payment method name.
	 */
	public static function get_payment_method_name( $slug ) {
		$methods = self::get_payment_methods();

		if ( isset( $methods[ $slug ] ) ) {
			return $methods[ $slug ];
		}

		return '';
	}

	public static function get_credit_methods() {
		return array( 'visa', 'mastercard', 'diners', 'discover', 'elo', 'amex' );
	}

	public static function get_store_credit_methods() {
		return array( 'visa', 'mastercard', 'diners', 'elo', 'amex' );
	}

	public static function get_credit_cards() {
		return array( 'visa', 'mastercard', 'diners', 'elo', 'amex' );
	}

	/**
	 * Get debit methods.
	 *
	 * @return array
	 */
	public static function get_debit_methods() {
		return array( 'visa' );
	}

	/**
	 * Set cURL custom settings for Cielo.
	 *
	 * @param  resource $handle The cURL handle returned by curl_init().
	 * @param  array    $r      The HTTP request arguments.
	 * @param  string   $url    The destination URL.
	 *
	 * @return void
	 */
	public function curl_settings( $handle, $r, $url ) {
		if ( isset( $r['sslcertificates'] ) && $this->get_certificate() === $r['sslcertificates'] && $this->get_api_url() === $url ) {
			curl_setopt( $handle , CURLOPT_SSLVERSION , 3 );
		}
	}

	/**
	 * Get the account data.
	 *
	 * @return array
	 */
	private function get_account_data() {
		if ( 'production' == $this->gateway->environment ) {
			return array(
				'number' => $this->gateway->number,
				'key'    => $this->gateway->key
			);
		} else {
			return array(
				'number' => $this->test_cielo_number,
				'key'    => $this->test_cielo_key
			);
		}
	}

	/**
	 * Get API URL.
	 *
	 * @return string
	 */
	public function get_api_url() {
		if ( 'production' == $this->gateway->environment ) {
			return $this->production_url;
		} else {
			return $this->test_url;
		}
	}

	/**
	 * Get certificate.
	 *
	 * @return string
	 */
	protected function get_certificate() {
		return plugin_dir_path( __FILE__ ) . 'certificates/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt';
	}

	/**
	 * Get the return URL.
	 *
	 * @return string
	 */
	protected function get_return_url() {
		global $woocommerce;

		// Backwards compatibility with WooCommerce version prior to 2.1.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			return WC()->api_request_url( 'WC_Cielo_Gateway' );
		} else {
			return $woocommerce->api_request_url( 'WC_Cielo_Gateway' );
		}
	}

	/**
	 * Get language.
	 *
	 * @return string
	 */
	protected function get_language() {
		$language = 'EN';

		if ( defined( 'WPLANG' ) && '' != WPLANG ) {
			$language = strtoupper( substr( 'pt_BR', 0, 2 ) );

			if ( ! in_array( $language, array( 'PT', 'EN', 'ES' ) ) ) {
				$language = 'EN';
			}
		}

		return $language;
	}

	/**
	 * Get the secure XML data for debug.
	 *
	 * @param  WC_Cielo_XML $xml
	 *
	 * @return WC_Cielo_XML
	 */
	protected function get_secure_xml_data( $xml ) {
		// Remove API data.
		if ( isset( $xml->{'dados-ec'} ) ) {
			unset( $xml->{'dados-ec'} );
		}

		// Remove card data.
		if ( isset( $xml->{'dados-portador'} ) ) {
			unset( $xml->{'dados-portador'} );
		}

		return $xml;
	}

	/**
	 * Get default error message.
	 *
	 * @return StdClass
	 */
	protected function get_default_error_message() {
		$error = new StdClass;
		$error->mensagem = __( 'An error has occurred while processing your payment, please try again or contact us for assistance.', 'cielo-woocommerce' );

		return $error;
	}

	/**
	 * Do remote requests.
	 *
	 * @param  string $data Post data.
	 *
	 * @return array        Remote response data.
	 */
	protected function do_request( $data ) {
		$params = array(
			'body'            => $data,
			'sslverify'       => true,
			'timeout'         => 40,
			'sslcertificates' => $this->get_certificate(),
			'headers'         => array(
				'Content-Type' => 'application/x-www-form-urlencoded'
			)
		);

		add_action( 'http_api_curl', array( $this, 'curl_settings' ), 10, 3 );
		$response = wp_remote_post( $this->get_api_url(), $params );
		remove_action( 'http_api_curl', array( $this, 'curl_settings' ), 10 );

		return $response;
	}

	/**
	 * Do transaction.
	 *
	 * @param  WC_Order $order          Order data.
	 * @param  string   $transaction_id Transaction ID.
	 * @param  string   $card_brand     Card brand slug.
	 * @param  int      $installments   Number of installments (use 0 for debit).
	 *
	 * @return array                    Transaction data.
	 */
	public function do_transaction( $order, $transaction_id, $card_brand, $installments ) {
		$account_data    = $this->get_account_data();
		$payment_product = '1';
		$order_total     = $order->order_total;
		$authorization   = $this->gateway->authorization;

		// Set the authorization.
		if ( 'visa' != $card_brand && 3 != $authorization ) {
			$authorization = 3;
		}

		// Set the order total with interest.
		if ( 'client' == $this->gateway->installment_type && $installments >= $this->gateway->interest ) {
			$order_total = $order->order_total * ( ( 100 + $this->gateway->interest_rate ) / 100 );
		}

		// Set the debit values.
		if ( in_array( $card_brand, self::get_debit_methods() ) && 0 == $installments ) {
			$order_total     = $order->order_total * ( ( 100 - $this->gateway->debit_discount ) / 100 );
			$payment_product = 'A';
			$installments    = '1';
			$authorization   = ( 3 == $authorization ) ? 2 : $authorization;
		}

		// Set the product when has installments.
		if ( 1 < $installments ) {
			$payment_product = '2';
		}

		$xml = new WC_Cielo_XML( '<?xml version="1.0" encoding="' . $this->charset . '"?><requisicao-transacao id="' . $transaction_id . '" versao="' . self::VERSION . '"></requisicao-transacao>' );
		$xml->add_account_data( $account_data['number'], $account_data['key'] );
		$xml->add_order_data( $order, $order_total, self::CURRENCY, $this->get_language() );
		$xml->add_payment_data( $card_brand, $payment_product, $installments );
		$xml->add_return_url( $this->get_return_url() );
		$xml->add_authorize( $authorization );
		$xml->add_capture( $this->gateway->capture );
		$xml->add_token_generation( 'false' );

		// Render the XML.
		$data = $xml->render();

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting a transaction for order ' . $order->get_order_number() . ' with the follow data: ' . print_r( $this->get_secure_xml_data( $xml ), true ) );
		}

		// Do the transaction request.
		$response = $this->do_request( 'mensagem=' . $data );

		// Request error.
		if ( is_wp_error( $response ) || ( isset( $response['response'] ) && 200 != $response['response']['code'] ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'An error occurred while requesting the transaction: ' . print_r( $response, true ) );
			}

			return $this->get_default_error_message();
		}

		// Get the transaction response data.
		try {
			$body = @new SimpleXmlElement( $response['body'], LIBXML_NOCDATA );
		} catch ( Exception $e ) {
			$body = '';

			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Error while parsing the Cielo response: ' . print_r( $e->getMessage(), true ) );
			}
		}

		// Error when getting the transaction response data.
		if ( empty( $body ) ) {
			return $this->get_default_error_message();
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Transaction successfully created for the order ' . $order->get_order_number() );
		}

		return $body;
	}
}
