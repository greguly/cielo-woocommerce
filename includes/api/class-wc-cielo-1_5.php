<?php
/**
 * WC Cielo API Class.
 */
class WC_Cielo_API_1_5 {

	/**
	 * API version.
	 */
	const VERSION = '1.3.0';

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
	 * @param WC_Cielo_API_1_5 $gateway
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
		$this->charset = get_bloginfo( 'charset' );
	}

	/**
	 * Set cURL custom settings for Cielo.
	 *
	 * @param  resource $handle The cURL handle returned by curl_init().
	 * @param  array    $r      The HTTP request arguments.
	 * @param  string   $url    The destination URL.
	 */
	public function curl_settings( $handle, $r, $url ) {
		if ( isset( $r['sslcertificates'] ) && $this->get_certificate() === $r['sslcertificates'] && $this->get_api_url() === $url ) {
			curl_setopt( $handle, CURLOPT_SSLVERSION, 4 );
		}
	}

	/**
	 * Get the status error.
	 *
	 * @param  int $id Status ID.
	 *
	 * @return string
	 */
	public function get_status( $id ) {

		$status = array(
			0 => true,  //Transaction created
			1 => true,  //Transaction ongoing
			2 => true,  //Transaction authenticated
			3 => false, //Transaction not authenticated
			4 => true,  //Transaction authorized
			5 => false, //Transaction not authorized
			6 => true,  //Transaction captured
			9 => false, //Transaction cancelled
			10 => true, //Transaction in authentication
			12 => false,//Transaction in cancellation
		);

		if ( isset( $status[ $id ] ) ) {
			return $status[ $id ];
		}

		return false;//Transaction failed
	}

	/**
	 * Get the status name.
	 *
	 * @param  int $id Status ID.
	 *
	 * @return string
	 */
	public function get_status_name( $id ) {

		$status = array(
			0 => _x('Transaction created', 'Transaction Status', 'cielo-woocommerce'),
			1 => _x('Transaction ongoing', 'Transaction Status', 'cielo-woocommerce'),
			2 => _x('Transaction authenticated', 'Transaction Status', 'cielo-woocommerce'),
			3 => _x('Transaction not authenticated', 'Transaction Status', 'cielo-woocommerce'),
			4 => _x('Transaction authorized', 'Transaction Status', 'cielo-woocommerce'),
			5 => _x('Transaction not authorized', 'Transaction Status', 'cielo-woocommerce'),
			6 => _x('Transaction captured', 'Transaction Status', 'cielo-woocommerce'),
			9 => _x('Transaction cancelled', 'Transaction Status', 'cielo-woocommerce'),
			10 => _x('Transaction in authentication', 'Transaction Status', 'cielo-woocommerce'),
			12 => _x('Transaction in cancellation', 'Transaction Status', 'cielo-woocommerce'),
		);

		if ( isset( $status[ $id ] ) ) {
			return $status[ $id ];
		}

		return _x( 'Transaction failed', 'Transaction Status', 'cielo-woocommerce' );

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
				'key'    => $this->gateway->key,
			);
		} else {
			if ( 'webservice' == $this->gateway->store_contract ) {
				return array(
					'number' => $this->test_store_number,
					'key'    => $this->test_store_key,
				);
			} else {
				return array(
					'number' => $this->test_cielo_number,
					'key'    => $this->test_cielo_key,
				);
			}
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
		return plugin_dir_path( __FILE__ ) . '../certificates/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt';
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
	 * Get language.
	 *
	 * @return string
	 */
	protected function get_language() {
		$language = strtoupper( substr( get_locale(), 0, 2 ) );

		if ( ! in_array( $language, array( 'PT', 'EN', 'ES' ) ) ) {
			$language = 'EN';
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
	 * Safe load XML.
	 *
	 * @param  string $source  XML source.
	 * @param  int    $options DOMDocument options.
	 *
	 * @return SimpleXMLElement|bool
	 */
	protected function safe_load_xml( $source, $options = 0 ) {
		$old    = null;
		$source = trim( $source );

		if ( '<' !== substr( $source, 0, 1 ) ) {
			return false;
		}

		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			$old = libxml_disable_entity_loader( true );
		}

		$dom    = new DOMDocument();
		$return = $dom->loadXML( $source, $options );

		if ( ! is_null( $old ) ) {
			libxml_disable_entity_loader( $old );
		}

		if ( ! $return ) {
			return false;
		}

		if ( isset( $dom->doctype ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Unsafe DOCTYPE Detected while XML parsing' );
			}

			return false;
		}

		return simplexml_import_dom( $dom );
	}
	
	/**
	 * Process webservice payment.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function process_webservice_payment( $order, $response ) {

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

        return Array(
            'valid' => $valid,
            'payment_url' => $payment_url,
        );

	}

	/**
	 * Process the order status.
	 *
	 * @param WC_Order $Order  Order data.
	 * @param int      $status Status ID.
	 * @param string   $status_note Order status note.
	 * @param string   $note   Custom order note.
	 */
	public function process_order_status( $order, $status, $status_note, $note = '' )	{

        // Order cancelled.
		if ( 9 == $status ) {
			$order->add_order_note( $status_note . '. ' . $note );

			$order->update_status( 'cancelled', $status_note );
			// Order failed.
		} elseif ( ( 1 != $status && 4 != $status && 6 != $status ) || -1 == $status ) {
			$order->add_order_note( $status_note . '. ' . $note );

			$order->update_status( 'failed', $status_note );
			// Order paid.
		} else {
			$order->add_order_note( $status_note . '. ' . $note );
			// Complete the payment and reduce stock levels.
			$order->payment_complete();
		}

	}	
		
	/**
	 * Process webservice payment with card brand.
	 *
	 * @param  Card_Brand String
	 *
	 * @return array
	 */
	public function process_webservice_payment_card_brand( $card_brand ) {

		// Validate credit card brand.
		if ( 'mastercard' === $card_brand ) {
			return 'maestro';
		} else if ( 'visa' === $card_brand ) {
			return 'visaelectron';
		} else {
			return $card_brand;
		}

	}

	/**
	 * Do remote requests.
	 *
	 * @param  string $data Post data.
	 *
	 * @return array        Remote response data.
	 */
	protected function do_request( $data = null ) {

		$params = array(
			'body'            => 'mensagem=' . $data,
			'sslverify'       => true,
			'timeout'         => 40,
			'sslcertificates' => $this->get_certificate(),
			'headers'         => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
		);

		add_action( 'http_api_curl', array( $this, 'curl_settings' ), 10, 3 );
		$response = wp_remote_post( $this->get_api_url(), $params );
		remove_action( 'http_api_curl', array( $this, 'curl_settings' ), 10 );

		return $response;
		
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
	public function do_transaction( $account_data, $payment_product, $order_total, $authorization, $order, $id, $card_brand, $installments = 0, $credit_card_data = array(), $gateway = '' ) {

		if ( in_array( $card_brand, $this->gateway->get_accept_authorization() ) && 3 != $this->gateway->authorization && ! ($gateway == 'cielo_debit') ) {
			$authorization = 3;
		}

        $xml = new WC_Cielo_XML( '<?xml version="1.0" encoding="' . $this->charset . '"?><requisicao-transacao id="' . $id . '" versao="' . self::VERSION . '"></requisicao-transacao>' );
        $xml->add_account_data( $account_data['number'], $account_data['key'] );

        if ( $credit_card_data ) {
            $xml->add_card_data( $credit_card_data['card_number'], $credit_card_data['card_expiration'], $credit_card_data['card_cvv'], $credit_card_data['name_on_card'] );
        }

        $xml->add_order_data( $order, $order_total, self::CURRENCY, $this->get_language() );
        $xml->add_payment_data( $card_brand, $payment_product, $installments );

        $xml->add_return_url( $this->gateway->get_api_return_url( $order ) );
        $xml->add_authorize( $authorization );

        // Check if sale capture is made through admin order page
        if (!$this->gateway->api->admin_sale_capture ()) {
            $xml->add_capture( 'true' );
        }

        $xml->add_token_generation( 'false' );

        // Render the XML.
        $xml_data = $xml->render();

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Requesting a transaction for order ' . $order->get_order_number() . ' with the follow data: ' . print_r( $this->get_secure_xml_data( $xml ), true ) );
        }

        // Do the transaction request.
        $response = $this->do_request( $xml_data );

        // Request error.
        if ( is_wp_error( $response ) || ( isset( $response['response'] ) && 200 != $response['response']['code'] ) ) {
            if ( 'yes' == $this->gateway->debug ) {
                $this->gateway->log->add( $this->gateway->id, 'An error occurred while requesting the transaction: ' . print_r( $response, true ) );
            }

            return $this->get_default_error_message();
        }

        // Get the transaction response data.
        $response_data = $this->safe_load_xml( $response['body'] );

        // Error when getting the transaction response data.
        if ( empty( $response_data ) ) {
            return $this->get_default_error_message();
        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Transaction successfully created for the order ' . $order->get_order_number() );
        }
        
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
	public function get_transaction_data( $order, $tid, $id, $account_data ) {

		//$account_data = $this->get_account_data();

		$xml = new WC_Cielo_XML( '<?xml version="1.0" encoding="' . $this->charset . '"?><requisicao-consulta id="' . $id . '" versao="' . self::VERSION . '"></requisicao-consulta>' );
		$xml->add_tid( $tid );
		$xml->add_account_data( $account_data['number'], $account_data['key'] );

		// Render the XML.
		$data = $xml->render();

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Checking the transaction status for order ' . $order->get_order_number() . '...' );
		}

		// Do the transaction request.
		$response = $this->do_request( $data );
		if ( is_wp_error( $response ) || ( isset( $response['response'] ) && 200 != $response['response']['code'] ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'An error occurred while checking the transaction status: ' . print_r( $response, true ) );
			}

			return $this->get_default_error_message();
		}

		// Get the transaction response data.
		$response_data = $this->safe_load_xml( $response['body'] );

		// Error when getting the transaction response data.
		if ( empty( $response_data ) ) {
			return $this->get_default_error_message();
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Recovered the order ' . $order->get_order_number() . ' data successfully' );
		}

		return $response_data;
	}

    /**
     * Return handler.
     *
     * @param WC_Order $order Order data.
     */
    public function return_handler( $response, $tid ) {

        // Set the error alert.
        if ( ! empty( $response->mensagem ) ) {
            if ( 'yes' == $this->debug ) {
                $this->log->add( $this->id, 'Cielo payment error: ' . print_r( $response->mensagem, true ) );
            }

            $this->add_error( (string) $response->mensagem );
        }

        // Update the order status.
        $status     = ! empty( $response->status ) ? intval( $response->status ) : -1;
        $order_note = "\n";

        if ( 'yes' == $this->debug ) {
            $this->log->add( $this->id, 'Cielo payment status: ' . $status );
        }

        // For backward compatibility!
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.12', '<=' ) ) {
            $order_note = "\n" . 'TID: ' . $tid . '.';
        }

        if ( ! empty( $response->{'forma-pagamento'} ) ) {
            $payment_method = $response->{'forma-pagamento'};

            $order_note .= "\n";
            $order_note .= __( 'Paid with', 'cielo-woocommerce' );
            $order_note .= ' ';
            $order_note .= $this->gateway->get_payment_method_name( (string) $payment_method->bandeira );
            $order_note .= ' ';

            if ( 'A' == $payment_method->produto ) {
                $order_note .= __( 'debit', 'cielo-woocommerce' );
            } elseif ( '1' == $payment_method->produto ) {
                $order_note .= __( 'credit at sight', 'cielo-woocommerce' );
            } else {
                $order_note .= sprintf( __( 'credit %dx', 'cielo-woocommerce' ), $payment_method->parcelas );
            }

            $order_note .= '.';
        }

        return Array(
            'status'     => $status,
            'order_note' => $order_note,
        );

    }

    /**
     * Return handler cancel.
     *
     * @param $woocommerce $woocommerce Order data.
     * @param String $status Order status.
     */
    public function return_handler_cancel( $woocommerce, $status ) {

        // Order cancelled.
        if ( 9 == $status ) {
            $message = __( 'Order canceled successfully.', 'cielo-woocommerce' );
            if ( function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( $message );
            } else {
                $woocommerce->add_message( $message );
            }
            if ( function_exists( 'wc_get_page_id' ) ) {
                return get_permalink( wc_get_page_id( 'shop' ) );
            } else {
                return get_permalink( woocommerce_get_page_id( 'shop' ) );
            }
        }

        return null;
        
    }
        
    /**
     * Do sale capture.
     *
     * @param  WC_Order $order Order data.
     * @param  string   $tid     Transaction ID.
     * @param  string   $id      Request ID.
     * @param  float    $amount  Amount for refund.
     *
     * @return array
     */
    public function do_sale_capture( $order, $tid, $id, $amount = 0 ) {
        $account_data = $this->get_account_data();
        $xml          = new WC_Cielo_XML( '<?xml version="1.0" encoding="' . $this->charset . '"?><requisicao-captura id="' . $id . '" versao="' . self::VERSION . '"></requisicao-captura>' );
        $xml->add_tid( $tid );
        $xml->add_account_data( $account_data['number'], $account_data['key'] );

        if ( $amount ) {
            $xml->add_value( $amount );
        }

        // Render the XML.
        $data = $xml->render();

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Capturing ' . $amount . ' from order ' . $order->get_order_number() . '...' );
        }

        // Do the request.
        $response = $this->do_request( $data );

        // Set error message.
        $error = new StdClass;
        $error->mensagem = __( 'An error occurred while trying to capture the sale, turn on the Cielo log option and try again.', 'cielo-woocommerce' );

        if ( is_wp_error( $response ) || ( isset( $response['response'] ) && 200 != $response['response']['code'] ) ) {
            if ( 'yes' == $this->gateway->debug ) {
                $this->gateway->log->add( $this->gateway->id, 'An error occurred while capturing the sale: ' . print_r( $response, true ) );
            }

            return $error;
        }

        // Get the transaction response data.
        $response_data = $this->safe_load_xml( $response['body'] );

        // Error when getting the transaction response data.
        if ( empty( $response_data ) ) {
            return $error;
        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Captured ' . $amount . ' from order ' . $order->get_order_number() . ' successfully!' );
        }

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
		$xml          = new WC_Cielo_XML( '<?xml version="1.0" encoding="' . $this->charset . '"?><requisicao-cancelamento id="' . $id . '" versao="' . self::VERSION . '"></requisicao-cancelamento>' );
		$xml->add_tid( $tid );
		$xml->add_account_data( $account_data['number'], $account_data['key'] );

		if ( $amount ) {
			$xml->add_value( $amount );
		}

		// Render the XML.
		$data = $xml->render();

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Refunding ' . $amount . ' from order ' . $order->get_order_number() . '...' );
		}

		// Do the request.
		$response = $this->do_request( $data );

		// Set error message.
		$error = new StdClass;
		$error->mensagem = __( 'An error occurred while trying to cancel the payment, turn on the Cielo log option and try again.', 'cielo-woocommerce' );

		if ( is_wp_error( $response ) || ( isset( $response['response'] ) && 200 != $response['response']['code'] ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'An error occurred while canceling the transaction: ' . print_r( $response, true ) );
			}

			return $error;
		}

		// Get the transaction response data.
		$response_data = $this->safe_load_xml( $response['body'] );

		// Error when getting the transaction response data.
		if ( empty( $response_data ) ) {
			return $error;
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Refunded ' . $amount . ' from order ' . $order->get_order_number() . ' successfully!' );
		}

		return $response_data;
	}
}
