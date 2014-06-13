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
	 * Language.
	 */
	const LANGUAGE = 'PT';

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
	 * Smallest Installment.
	 *
	 * @var int
	 */
	protected static $smallest_installment = 5;

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
			'visa'       => __( 'Visa', 'woocommerce-cielo' ),
			'mastercard' => __( 'MasterCard', 'woocommerce-cielo' ),
			'diners'     => __( 'Diners', 'woocommerce-cielo' ),
			'discover'   => __( 'Discover', 'woocommerce-cielo' ),
			'elo'        => __( 'Elo', 'woocommerce-cielo' ),
			'amex'       => __( 'American Express', 'woocommerce-cielo' )
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

	public static function get_debit_methods() {
		return array( 'visa' );
	}

	public static function get_smallest_installment() {
		return self::$smallest_installment;
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
	 * @param  WC_Order $order Order data.
	 *
	 * @return [type]        [description]
	 */
	public function do_transaction( $order, $id ) {
		$account_data = $this->get_account_data();

		$xml = new WC_Cielo_XML( '<?xml version="1.0" encoding="' . $this->charset . '"?><requisicao-transacao id="' . $id . '" versao="' . self::VERSION . '"></requisicao-transacao>' );
		$xml->add_account_data( $account_data['number'], $account_data['key'] );
		$xml->add_order_data( $order, self::CURRENCY, self::LANGUAGE );
		$xml->add_payment_data( 'visa', '1', '1' );
		$xml->add_return_url( 'testando' );
		$xml->add_authorize( '3' );
		$xml->add_capture( 'false' );
		$xml->add_token_generation( 'false' );
		$data = 'mensagem=' . $xml->render();

		return $this->do_request( $data );
	}
}
