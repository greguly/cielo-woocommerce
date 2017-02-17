<?php
use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

/**
 * WC Cielo API Class.
 */
class WC_Cielo_API extends WC_Settings_API {

	/**
	 * API version.
	 */
	const VERSION = '1.3.0';

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
	public $api_version;

	/**
	 * Environment type.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Charset.
	 *
	 * @var string
	 */
	protected $charset = 'ISO-8859-1';

	/**
	 * Cielo WooCommerce API.
	 *
	 * @var WC_Cielo_API_Version
	 */
	public $api = null;

	/**
	 * Constructor.
	 *
	 * @param WC_Cielo_API
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;
		$this->charset = get_bloginfo( 'charset' );

		// Get API version
		$this->api_version = maybe_unserialize(get_option('woocommerce_cielo_general_settings_settings'))['api_version'];

		// Get version list from json file
		include_once dirname( __FILE__ ) . '/version/class-wc-cielo-version.php';
		include_once dirname( __FILE__ ) . WC_Cielo_Version::getVersion('path', $this->api_version);

		// Get API Class name, selected in General Settings and class in Json file
		$api_class = WC_Cielo_Version::getVersion('class', $this->api_version);
        //$this->gateway->log->add( $this->gateway->id, 'Create API ' . $api_class );

		// Instantiate API, according with version selected in General Settings
		$this->api = new $api_class($this->gateway);

	}

    /**
     * Cielo Return if enable admin sale capture
     *
     */
    public function admin_sale_capture () {

        // Check value to capture sale
        $general_settings = maybe_unserialize(get_option('woocommerce_cielo_general_settings_settings')) ;

        // Check if enabled is using default value
        if ( array_key_exists('admin_sale_capture', $general_settings) ) {
            if ($general_settings['admin_sale_capture'] == 'yes') {
                $this->gateway->log->add( $this->gateway->id, 'Gateway: ' . $this->gateway->id );
                return true;
            }
        } else {
            return false;
        }

        return false;

    }

    /**
	 * Get the account data.
	 *
	 * @return array
	 */
	private function get_account_data() {

		return array(
			'environment' => $this->gateway->environment,
			'number' => $this->gateway->number,
			'key'    => $this->gateway->key,
		);

	}

	/**
	 * Get credit card brand.
	 *
	 * @param  string $number
	 *
	 * @return string
	 */
	public function get_card_brand( $number ) {
		$number = preg_replace( '([^0-9])', '', $number );
		$brand  = '';

		// https://gist.github.com/arlm/ceb14a05efd076b4fae5
		$supported_brands = array(
			'visa'       => '/^4\d{12}(\d{3})?$/',
			'mastercard' => '/^(5[1-5]\d{4}|677189)\d{10}$/',
			'diners'     => '/^3(0[0-5]|[68]\d)\d{11}$/',
			'discover'   => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
			'elo'        => '/^((((636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})$/',
			'amex'       => '/^3[47]\d{13}$/',
			'jcb'        => '/^(?:2131|1800|35\d{3})\d{11}$/',
			'aura'       => '/^(5078\d{2})(\d{2})(\d{11})$/',
			'hipercard'  => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
			'maestro'    => '/^(?:5[0678]\d\d|6304|6390|67\d\d)\d{8,15}$/',
		);

		foreach ( $supported_brands as $key => $value ) {
			if ( preg_match( $value, $number ) ) {
				$brand = $key;
				break;
			}
		}

		return $brand;
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
	 * Do transaction.
	 *
	 * @param  WC_Order $order            Order data.
	 * @param  string   $id               Request ID.
	 * @param  string   $card_brand       Card brand slug.
	 * @param  int      $installments     Number of installments (use 0 for debit).
	 * @param  array    $credit_card_data Credit card data for the webservice.
	 * @param  bool     $is_debit         Check if is debit or credit.
	 *
	 * @return SimpleXmlElement|StdClass Transaction data.
	 */
	public function do_transaction( $order, $id, $card_brand, $installments = 0, $credit_card_data = array(), $is_debit = false ) {
		
		$account_data    = $this->get_account_data();
		$payment_product = '1';
		$order_total     = (float) $order->get_total();
		$authorization   = $this->gateway->authorization;

		$response_data = null;

		// Set the authorization.
		if ( in_array( $card_brand, $this->gateway->get_accept_authorization() ) && 3 != $authorization && ! $is_debit ) {
			$authorization = 3;
		}

		// Set the order total with interest.
		if ( isset( $this->gateway->installment_type ) && 'client' == $this->gateway->installment_type && $installments >= $this->gateway->interest ) {
			$interest_rate        = $this->gateway->get_valid_value( $this->gateway->interest_rate ) / 100;
			$interest_total       = $order_total * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $installments ) ) ) );
			$interest_order_total = $interest_total * $installments;

			if ( $order_total < $interest_order_total ) {
				$order_total = round( $interest_order_total, 2 );
			}
		}

		// Set the debit values.
		if ( $is_debit ) {
			$order_total     = $order_total * ( ( 100 - $this->gateway->get_valid_value( $this->gateway->debit_discount ) ) / 100 );
			$payment_product = 'A';
			$installments    = '1';
		}

		// Set the product when has installments.
		if ( 1 < $installments ) {
			$payment_product = '2';
		}

		// Execute transaction accordin API
		$response_data = $this->api->do_transaction(
			$account_data,
			$payment_product,
			$order_total,
			$authorization,
			$order,
			$id,
			$card_brand,
			$installments,
			$credit_card_data,
			$is_debit
		);

		return $response_data;

	}

	/**
	 * Do sale capture.
	 *
	 * @param  WC_Order $order Order data.
	 * @param  string   $tid     Transaction ID.
	 * @param  string   $id      Request ID.
	 * @param  float    $amount  Amount for capture.
	 *
	 * @return array
	 */
	public function do_sale_capture( $order, $tid, $id, $amount = 0 ) {
		$account_data = $this->get_account_data();
		$this->gateway->log->add( $this->gateway->id, 'Começo ' . $this->gateway->number );

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Capturing ' . $amount . ' from order ' . $order->get_order_number() . '...' );
		}

		$response_data = $this->api->do_sale_capture(
			$order,
			$tid,
			$id,
			$amount,
			$account_data
		);

        if (isset($response_data->mensagem)) {
            return array(
                'error'     => true,
                'cielocode' => $response_data->cielocode,
                'message'   => $response_data->mensagem,
            );
        } else {
            return array(
                'error'     => false,
                'cielocode' => isset($response_data->captura) ? $response_data->captura->codigo : $response_data->ReturnCode,
                'message'   => isset($response_data->captura) ? $response_data->captura->mensagem : $response_data->ReturnMessage,
            );
        }

//		return $response_data;

	}

	/**
	 * Get transaction data.
	 *
	 * @param  WC_Order $order Order data.
	 * @param  string   $tid     Transaction ID.
	 * @param  string   $id      Request ID.
	 *
	 * @return SimpleXmlElement|StdClass Transaction data.
	 */
	public function get_transaction_data( $order, $tid, $id ) {
		$account_data = $this->get_account_data();

		$response_data = $this->api->get_transaction_data( $order, $tid, $id, $account_data );

		return $response_data;
	}

	/**
	 * Do transaction cancellation.
	 *
	 * @param  WC_Order $order Order data.
	 * @param  string   $tid     Transaction ID.
	 * @param  string   $id      Request ID.
	 * @param  float    $amount  Amount for refund.
	 *
	 * @return array
	 */
	public function do_transaction_cancellation( $order, $tid, $id, $amount = 0 ) {
		$account_data = $this->get_account_data();

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Refunding ' . $amount . ' from order ' . $order->get_order_number() . '...' );
		}

		$response_data = $this->api->do_transaction_cancellation(
			$order,
			$tid,
			$id,
			$amount,
			$account_data
		);

		return $response_data;

	}
}
