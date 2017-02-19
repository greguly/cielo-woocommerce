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
        $this->gateway->log->add( $this->gateway->id, 'do_transaction_cancellation');

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
		$this->gateway->log->add( $this->gateway->id, 'do_transaction_cancellation');

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
