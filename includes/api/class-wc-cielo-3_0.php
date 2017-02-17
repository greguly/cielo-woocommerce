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

//        $returnCode = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['returnCode'] ) ;
//        $returnMessage = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['returnMessage'] ) ;
        $status        = $response->getPayment()->getStatus() ;
        //$links = json_encode( $response->jsonSerialize()['payment']->jsonSerialize()['links'] ) ;
        $links = $response->getPayment()->getAuthenticationUrl();

        // Set the error alert.
        //if ( !( trim($returnCode, '"') == "4" ) ) {
        if (isset($status)) {
            if (!$this->gateway->get_status(trim($status, '"'))) {
                $this->gateway->add_error( (string)$this->gateway->get_status_name( $status ) );
                $valid = false;
            }
        }

        // Save the tid.
        if (!empty($paymentId)) {
            update_post_meta($order->id, '_transaction_id', $paymentId);
        }

        // Set the transaction URL.
        if (!empty($links)) {
            $payment_url = (string)$links;
        } else {
            $payment_url = str_replace('&amp;', '&', urldecode($this->gateway->get_api_return_url($order)));
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
	public function do_request( $account_data ) {

		// Configure o ambiente
		if ( 'production' == $this->gateway->environment ) {
			$this->environment = $environment = Environment::production();
		} else {
            $this->environment = $environment = Environment::sandbox();
        }

        // Configure seu merchant
        $this->merchant = new Merchant( $account_data['number'], $account_data['key'] );

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

        $this->gateway->log->add( $this->gateway->id, 'Bandeira: '.$card_brand );

        $response_data = null;

        // Create the environment
        $this->do_request($account_data);

        $sale = new Sale($id);

        $customer = $sale->customer( trim($order->billing_first_name) . ' ' . trim($order->billing_last_name) );

        if ($installments > 0) {
            $payment = $sale->payment(number_format($order_total, 2, '', ''), $installments);
        } else {
            $payment = $sale->payment(number_format($order_total, 2, '', ''));
        }

        if (!$is_debit) {
            $card = $payment->creditCard($credit_card_data['card_cvv'], $card_brand);
        } else {
            // Define URL redirect back to store after bank transaction
            $payment->setReturnUrl( str_replace( '&amp;', '&', urldecode( $this->gateway->get_api_return_url( $order ) ) ) );

            $card = $payment->debitCard($credit_card_data['card_cvv'], $card_brand);
        }

        $card->setExpirationDate( $credit_card_data['card_expiration'] );
        $card->setCardNumber( $credit_card_data['card_number'] );
        $card->setHolder( $credit_card_data['name_on_card'] );

        try {
            $sale   = (new CieloEcommerce($this->merchant, $this->environment))->createSale($sale);
            $status = $sale->getPayment()->getStatus() ;
            $this->gateway->log->add($this->gateway->id, json_encode($sale->jsonSerialize()) );

            // Get status message
            if (isset($status)) {
                // Check if a message is a error
                if ($this->gateway->get_status(trim($status, '"'))) {
                    // Verify is Credit Gateway only
                    if ($this->gateway->id == 'cielo_credit') {
                        // Check if capture sale is made by Admin Order Page
                        if (!$this->gateway->api->admin_sale_capture()) {
                            $this->gateway->log->add($this->gateway->id, 'Automatic sale capture');
                            // Automatic Capture Sale
                            $sale = $this->do_sale_capture_internal($order, '', $id, $order_total, $account_data);
                        }
                    }
                }
            }

            $response_data = $sale;

        } catch (CieloRequestException $e) {
            // Em caso de erros de integração, podemos tratar o erro aqui.
            // os códigos de erro estão todos disponíveis no manual de integração.
            $error = $e->getCieloError();

            $this->gateway->log->add( $this->gateway->id, 'Error - Message: ' . $error->getMessage() );
            $this->gateway->log->add( $this->gateway->id, 'Erro - Code: ' . $error->getCode() );

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
            $this->gateway->log->add( $this->gateway->id, 'Error - Code: ' . $error->getCode() . ' Message: ' . $error->getMessage() );

		}

		return $response_data;

	}

    /**
     * Return handler.
     *
     * @param WC_Order $order Order data.
     */
    public function return_handler(Sale $response, $tid ) {

        $this->gateway->log->add($this->gateway->id, 'return_handler' );

        $status = $response->getPayment()->getStatus() ;

        if (isset($status)) {
            // Set the error alert.
            if (!$this->gateway->get_status(trim($status, '"'))) {
                if ('yes' == $this->gateway->debug) {
                    $this->gateway->log->add($this->gateway->id, 'Cielo payment error: ' . $this->gateway->get_status_name( $status ) );
                }
                $this->gateway->add_error( (string)$this->gateway->get_status_name( $status ) );
            }
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

        if (!empty($response->getPayment())) {
            $payment_type = $response->getPayment()->getType();

            $payment_method = $response->getPayment()->{"get" . $payment_type}();

            $order_note .= "\n";
            $order_note .= __('Paid with', 'cielo-woocommerce');
            $order_note .= ' ';
            $order_note .= $this->gateway->get_payment_method_name( strtolower( (string)$payment_method->getBrand() ) );
            $order_note .= ' ';

            if ('DebitCard' == $payment_type) {
                $order_note .= __('debit', 'cielo-woocommerce');
            } elseif ( ('CreditCard' == $payment_type) && ( (int)$response->getPayment()->getInstallments() == 1 ) ) {
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

        try {

            $sale = (new CieloEcommerce($this->merchant, $this->environment))->cancelSale(trim($tid, '"'), number_format( $amount, 2, '', '' ));

        } catch (CieloRequestException $e) {

            $cieloerror = $e->getCieloError();

        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Refunding ' . $amount . ' from order ' . $order->get_order_number() . '...' );
        }

        // Set error message.
		$error = new StdClass;
		$error->mensagem = __( 'An error occurred while trying to cancel the payment, turn on the Cielo log option and try again.', 'cielo-woocommerce' );

        if (isset($cieloerror)) {
            $error->cielocode = $cieloerror->getCode();
        }

        if ( !isset( $sale ) ) {
            if ( 'yes' == $this->gateway->debug ) {
                $this->gateway->log->add( $this->gateway->id, 'An error occurred while canceling the transaction: Code: ' . $cieloerror->getCode() . ' - Message: ' . $cieloerror->getMessage() );
            }
            return $error;
        }

        // Error when getting the transaction response data.
        if ( !isset( $sale ) && !isset( $cieloerror ) ) {
            return $error;
        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Refunded ' . $amount . ' from order ' . $order->get_order_number() . ' successfully!' );
        }

        return $sale->jsonSerialize();

	}
}
