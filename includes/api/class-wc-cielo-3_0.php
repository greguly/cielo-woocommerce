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
class WC_Cielo_API_3_0 {

	/**
	 * API version.
	 */
	const VERSION = '3.0';

	/**
	 * Gateway class.
	 *
	 * @var WC_Cielo_Gateway
	 */
	protected $gateway;

	/**
	 * Environment type.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Merchant ID and Key.
	 *
	 * @var string
	 */
	public $merchant;

	/**
	 * Charset.
	 *
	 * @var string
	 */
	protected $charset = 'ISO-8859-1';

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
     * Process webservice payment.
     *
     * @param  WC_Order $order
     *
     * @return array
     */
    public function process_webservice_payment( $valid, $order, $response ) {

        //$tid = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['tid'] ) ;
        $paymentId = json_encode( $response->getPayment()->getPaymentId() ) ;

        $returnCode = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['returnCode'] ) ;
        $returnMessage = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['returnMessage'] ) ;
        $links = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['links'] ) ;

        // Set the error alert.
        if ( !( str_replace('"', '', $returnCode) == "4" ) ) {

            $this->add_error((string)$returnMessage);
            $valid = false;

        }

        // Save the tid.
        if (!empty($paymentId)) {
            update_post_meta($order->id, '_transaction_id', $paymentId);
        }

        // Set the transaction URL.
        if (!empty($response->{'links'})) {
            $payment_url = (string)$response->{'links'};
        } else {
            $payment_url = str_replace('&amp;', '&', urldecode($this->gateway->get_api_return_url($order)));
        }
        $this->gateway->log->add( $this->gateway->id, 'Linha: ' . __LINE__. ' $payment_url: ' . $payment_url );

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
	public function do_request( $account_data ) {

		//$account_data = $this->get_account_data();

		// Configure o ambiente
		if ( 'production' == $this->gateway->environment ) {
			$this->environment = $environment = Environment::production();
		} else {

            $this->environment = $environment = Environment::sandbox();
        }

        // Configure seu merchant
        $this->merchant = new Merchant( $account_data['number'], $account_data['key'] );
        $this->gateway->log->add( $this->gateway->id, 'Linha: ' . __LINE__. ' do_request_version_3_0 sandbox ' . $account_data['number'] );

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
	public function do_transaction(  $account_data, $payment_product, $order_total, $authorization, $order, $id, $card_brand, $installments = 0, $credit_card_data = array(), $is_debit = false ) {

        $this->do_request($account_data);

        $sale = new Sale($id);

        $customer = $sale->customer( trim($order->billing_first_name) . ' ' . trim($order->billing_last_name) );

        //$order->setBirthDate();
        //$customer->setBirthDate();

        $payment = $sale->payment($order_total, $installments);

        $payment->setType( (!$is_debit) ? Payment::PAYMENTTYPE_CREDITCARD : Payment::PAYMENTTYPE_DEBITCARD )
            ->creditCard( $credit_card_data['card_cvv'], "Visa" )
            ->setExpirationDate( $credit_card_data['card_expiration'] )
            ->setCardNumber( $credit_card_data['card_number'] )
            ->setHolder( $credit_card_data['name_on_card'] );

        try {
            $sale = (new CieloEcommerce($this->merchant, $this->environment))->createSale($sale);

            if (!$this->gateway->api->admin_sale_capture ()) {
                $sale = $this->do_sale_capture_internal($order, '', $id, $order_total, $account_data);
            }

            $response_data = $sale;

        } catch (CieloRequestException $e) {
            // Em caso de erros de integração, podemos tratar o erro aqui.
            // os códigos de erro estão todos disponíveis no manual de integração.
            $error = $e->getCieloError();

            $this->gateway->log->add( $this->gateway->id, 'Erro: ' . $error->getMessage() );

        }

		return $response_data;
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
		$sale = null;
		$response_data = null;

		$this->do_request($account_data);

		try {

			$sale = (new CieloEcommerce($this->merchant, $this->environment))->getSale( str_replace('"', '', $tid) );
			$response_data = $sale;

		} catch (CieloRequestException $e) {

			$error = $e->getCieloError();
			$this->gateway->log->add( $this->gateway->id,  'Linha: ' . __LINE__. ' Erro: ' . $error );

		}

		return $response_data;

        
	}

    /**
     * Return handler.
     *
     * @param WC_Order $order Order data.
     */
    public function return_handler( $response, $tid ) {

        $this->gateway->log->add($this->gateway->id, 'return_handler' );

        $paymentId = json_encode( $response->getPayment()->getPaymentId() ) ;

        $returnCode    = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['returnCode'] ) ;
        $status        = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['status'] ) ;
        $returnMessage = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['returnMessage'] ) ;
        $links         = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['links'] ) ;

        $this->gateway->log->add($this->gateway->id, 'Return Code: ' . json_encode($response));
        $this->gateway->log->add($this->gateway->id, 'Return Code: ' . json_encode($response->jsonSerialize()['payment']));
//                $this->log->add($this->id, 'Status payment: ' . $status);
//                $this->log->add($this->id, 'Return: ' . __LINE__ . ' - ' . str_replace('"', '', $returnCode));

        // Set the error alert.
        if ( trim(str_replace('"', '', $returnCode)) != '4' ) {
            if ('yes' == $this->gateway->debug) {
                $this->gateway->log->add($this->gateway->id, 'Cielo payment error: ' . print_r($returnMessage, true));
            }

            $this->gateway->add_error((string)$returnMessage);
        }

        // Update the order status.
        $status = !empty($status) ? intval($status) : -1;
        $order_note = "\n";

        if ('yes' == $this->debug) {
            $this->gateway->log->add($this->gateway->id, 'Cielo payment status: ' . $status);
        }

        // For backward compatibility!
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1.12', '<=')) {
            $order_note = "\n" . 'TID: ' . $tid . '.';
        }

        if (!empty($response->{'forma-pagamento'})) {
            $payment_method = $response->{'forma-pagamento'};

            $order_note .= "\n";
            $order_note .= __('Paid with', 'cielo-woocommerce');
            $order_note .= ' ';
            $order_note .= $this->gateway->get_payment_method_name((string)$payment_method->bandeira);
            $order_note .= ' ';

            if ('A' == $payment_method->produto) {
                $order_note .= __('debit', 'cielo-woocommerce');
            } elseif ('1' == $payment_method->produto) {
                $order_note .= __('credit at sight', 'cielo-woocommerce');
            } else {
                $order_note .= sprintf(__('credit %dx', 'cielo-woocommerce'), $payment_method->parcelas);
            }

            $order_note .= '.';
        }

        return Array(
            'status'     => $status,
            'order_note' => $order_note,
        );

    }

    /**
     * Do sale capture internal.
     *
     * @param  WC_Order $order Order data.
     * @param  string   $tid     Transaction ID.
     * @param  string   $id      Request ID.
     * @param  float    $amount  Amount for refund.
     *
     * @return array
     */
    public function do_sale_capture_internal( $order, $tid, $id, $amount = 0, $account_data ) {

        return (new CieloEcommerce($this->merchant, $this->environment))->captureSale(str_replace('"', '', $id), number_format( $amount, 2, '', '' ), 0, true);

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
    public function do_sale_capture( $order, $tid, $id, $amount = 0, $account_data ) {
        $sale = null;

        $this->do_request($account_data);

        try {

            $sale = $this->do_sale_capture_internal( $order, $tid, $id, $amount, $account_data );

        //} catch (CieloRequestException $e) {
        } catch (CieloRequestException $e) {

            $cieloerror = $e->getCieloError();

        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Capturing ' . $amount . ' from order ' . $order->get_order_number() . '...' );
        }

        // Set error message.
        $error = new StdClass;
        $error->mensagem = __( 'An error occurred while trying to capturing the sale, turn on the Cielo log option and try again.', 'cielo-woocommerce' );
        if (isset($cieloerror)) {
            $error->cielocode = $cieloerror->getCode();
        }

//        if ( is_wp_error( $sale ) || ( isset( $sale['response'] ) && 200 != $sale['response']['code'] ) ) {
//        }
        if ( !isset( $sale ) ) {
            if ( 'yes' == $this->gateway->debug ) {
                $this->gateway->log->add( $this->gateway->id, 'An error occurred while capturing the transaction: Code: ' . $cieloerror->getCode() . ' - Message: ' . $cieloerror->getMessage() );
            }
            return $error;
        }

        // Error when getting the transaction response data.
        if ( !isset( $sale ) && !isset( $cieloerror ) ) {
            return $error;
        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Captured ' . $amount . ' from order ' . $order->get_order_number() . ' successfully!' );
        }

        return $sale->jsonSerialize();
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
	public function do_transaction_cancellation( $order, $tid, $id, $amount = 0, $account_data ) {
        $this->do_request($account_data);

        $this->gateway->log->add( $this->gateway->id, 'Cancelation: ' );

        try {

            $sale = (new CieloEcommerce($this->merchant, $this->environment))->cancelSale($tid, $amount);

        } catch (CieloRequestException $e) {

            $error = $e->getCieloError();
            $this->gateway->log->add( $this->gateway->id,  'Linha: ' . __LINE__. ' Erro: ' . $error );

        }

        $this->gateway->log->add( $this->gateway->id, 'Sale Cancelation: ' . $sale->jsonSerialize());

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Refunding ' . $amount . ' from order ' . $order->get_order_number() . '...' );
        }

        // Set error message.
		$error = new StdClass;
		$error->mensagem = __( 'An error occurred while trying to cancel the payment, turn on the Cielo log option and try again.', 'cielo-woocommerce' );

		if ( is_wp_error( $sale ) || ( isset( $sale['response'] ) && 200 != $sale['response']['code'] ) ) {
            
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'An error occurred while canceling the transaction: ' . print_r( $sale, true ) );
			}

			return $error;
            
		}

		// Get the transaction response data.
		$response_data = $sale['body'] ;

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
