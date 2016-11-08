<?php
/**
 * WC Cielo Helper Class.
 */
abstract class WC_Cielo_Helper extends WC_Payment_Gateway {

	/**
	 * Get payment methods.
	 *
	 * @return array
	 */
	public function get_payment_methods() {
		return array(
			// Credit.
			'visa'         => __( 'Visa', 'cielo-woocommerce' ),
			'mastercard'   => __( 'MasterCard', 'cielo-woocommerce' ),
			'diners'       => __( 'Diners', 'cielo-woocommerce' ),
			'discover'     => __( 'Discover', 'cielo-woocommerce' ),
			'elo'          => __( 'Elo', 'cielo-woocommerce' ),
			'amex'         => __( 'American Express', 'cielo-woocommerce' ),
			'jcb'          => __( 'JCB', 'cielo-woocommerce' ),
			'aura'         => __( 'Aura', 'cielo-woocommerce' ),

			// Debit
			'visaelectron' => __( 'Visa Electron', 'cielo-woocommerce' ),
			'maestro'      => __( 'Maestro', 'cielo-woocommerce' ),
		);
	}

	/**
	 * Get payment method name.
	 *
	 * @param  string $slug Payment method slug.
	 *
	 * @return string       Payment method name.
	 */
	public function get_payment_method_name( $slug ) {
		$methods = $this->get_payment_methods();

		if ( isset( $methods[ $slug ] ) ) {
			return $methods[ $slug ];
		}

		return $slug;
	}

	/**
	 * Get available methods options.
	 *
	 * @return array
	 */
	public function get_available_methods_options() {
		$methods = array();

		foreach ( $this->methods as $method ) {
			$methods[ $method ] = $this->get_payment_method_name( $method );
		}

		return $methods;
	}

	/**
	 * Get the accepted brands in a text list.
	 *
	 * @param  array $methods
	 *
	 * @return string
	 */
	public function get_accepted_brands_list( $methods ) {
		$total = count( $methods );
		$count = 1;
		$list  = '';

		foreach ( $methods as $method ) {
			$name = $this->get_payment_method_name( $method );

			if ( 1 == $total ) {
				$list .= $name;
			} else if ( $count == ( $total - 1 ) ) {
				$list .= $name . ' ';
			} else if ( $count == $total ) {
				$list .= sprintf( __( 'and %s', 'cielo-woocommerce' ), $name );
			} else {
				$list .= $name . ', ';
			}

			$count++;
		}

		return $list;
	}

	/**
	 * Get methods who accepts authorization.
	 *
	 * @return array
	 */
	public function get_accept_authorization() {
		return array( 'visa', 'mastercard' );
	}

	/**
	 * Get valid value.
	 * Prevents users from making shit!
	 *
	 * @param  string|int|float $value
	 *
	 * @return int|float
	 */
	public function get_valid_value( $value ) {
		$value = str_replace( '%', '', $value );
		$value = str_replace( ',', '.', $value );

		return $value;
	}

	/**
	 * Get the order API return URL.
	 *
	 * @param  WC_Order $order Order data.
	 *
	 * @return string
	 */
	public function get_api_return_url( $order ) {
		global $woocommerce;

		// Backwards compatibility with WooCommerce version prior to 2.1.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$url = WC()->api_request_url( get_class( $this ) );
		} else {
			$url = $woocommerce->api_request_url( get_class( $this ) );
		}

		return urlencode( add_query_arg( array( 'key' => $order->order_key, 'order' => $order->id ), $url ) );
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
			0  => _x( 'Transaction created', 'Transaction Status', 'cielo-woocommerce' ),
			1  => _x( 'Transaction ongoing', 'Transaction Status', 'cielo-woocommerce' ),
			2  => _x( 'Transaction authenticated', 'Transaction Status', 'cielo-woocommerce' ),
			3  => _x( 'Transaction not authenticated', 'Transaction Status', 'cielo-woocommerce' ),
			4  => _x( 'Transaction authorized', 'Transaction Status', 'cielo-woocommerce' ),
			5  => _x( 'Transaction not authorized', 'Transaction Status', 'cielo-woocommerce' ),
			6  => _x( 'Transaction captured', 'Transaction Status', 'cielo-woocommerce' ),
			9  => _x( 'Transaction cancelled', 'Transaction Status', 'cielo-woocommerce' ),
			10 => _x( 'Transaction in authentication', 'Transaction Status', 'cielo-woocommerce' ),
			12 => _x( 'Transaction in cancellation', 'Transaction Status', 'cielo-woocommerce' ),
		);

		if ( isset( $status[ $id ] ) ) {
			return $status[ $id ];
		}

		return _x( 'Transaction failed', 'Transaction Status', 'cielo-woocommerce' );
	}

	/**
	 * Get order total.
	 *
	 * @return float
	 */
	public function get_order_total() {
		global $woocommerce;

		$order_total = 0;
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
		} else {
			$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		}

		// Gets order total from "pay for order" page.
		if ( 0 < $order_id ) {
			$order      = new WC_Order( $order_id );
			$order_total = (float) $order->get_total();

			// Gets order total from cart/checkout.
		} elseif ( 0 < $woocommerce->cart->total ) {
			$order_total = (float) $woocommerce->cart->total;
		}

		return $order_total;
	}

	/**
	 * Get logger.
	 *
	 * @return WC_Logger instance.
	 */
	public function get_logger() {
		if ( class_exists( 'WC_Logger' ) ) {
			return new WC_Logger();
		} else {
			global $woocommerce;
			return $woocommerce->logger();
		}
	}

	/**
	 * Get log file path
	 *
	 * @return string
	 */
	public function get_log_file_path() {
		if ( function_exists( 'wc_get_log_file_path' ) ) {
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'cielo-woocommerce' ) . '</a>';
		}

		return '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>';
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		return ( 'BRL' == get_woocommerce_currency() );
	}

	/**
	 * Check the environment.
	 *
	 * @return bool
	 */
	public function check_environment() {
		if ( 'test' == $this->environment ) {
			return true;
		}

		// For production.
		return ( ! empty( $this->methods ) && ! empty( $this->number ) && ! empty( $this->key ) );
	}

	/**
	 * Check settings for webservice solution.
	 *
	 * @return bool
	 */
	public function checks_for_webservice() {
		if ( 'webservice' != $this->store_contract ) {
			return true;
		}

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2.11', '<=' ) ) {
			return false;
		}

		if ( 'test' == $this->environment ) {
			return true;
		}

		return 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) && is_ssl();
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = parent::is_available() &&
					$this->check_environment() &&
					$this->using_supported_currency() &&
					$this->checks_for_webservice();

		return $available;
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'wc-cielo-admin', plugins_url( 'assets/js/admin/admin' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );

		include dirname( __FILE__ ) . '/views/html-admin-page.php';
	}

	/**
	 * Add error messages in checkout.
	 *
	 * @param string $message Error message.
	 */
	public function add_error( $message ) {
		global $woocommerce;

		$title = '<strong>' . esc_attr( $this->title ) . ':</strong> ';

		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $title . $message, 'error' );
		} else {
			$woocommerce->add_error( $title . $message );
		}
	}

	/**
	 * Get debit discount.
	 *
	 * @param  float $order_total Order total.
	 *
	 * @return float
	 */
	public function get_debit_discount( $order_total = 0 ) {
		$debit_total = $order_total * ( ( 100 - $this->get_valid_value( $this->debit_discount ) ) / 100 );

		return $debit_total;
	}

	/**
	 * Get installments HTML.
	 *
	 * @param  float  $order_total Order total.
	 * @param  string $type        'select' or 'radio'.
	 *
	 * @return string
	 */
	public function get_installments_html( $order_total = 0, $type = 'select' ) {
		$html         = '';
		$installments = apply_filters( 'wc_cielo_max_installments', $this->installments, $order_total );

		if ( '1' == $installments ) {
			return $html;
		}

		if ( 'select' == $type ) {
			$html .= '<select id="cielo-installments" name="cielo_credit_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">';
		}

		$interest_rate = $this->get_valid_value( $this->interest_rate ) / 100;

		for ( $i = 1; $i <= $installments; $i++ ) {
			$credit_total    = $order_total / $i;
			$credit_interest = sprintf( __( 'no interest. Total: %s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $order_total ) ) );
			$smallest_value  = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

			if ( 'client' == $this->installment_type && $i >= $this->interest && 0 < $interest_rate ) {
				$interest_total = $order_total * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $i ) ) ) );
				$interest_order_total = $interest_total * $i;

				if ( $credit_total < $interest_total ) {
					$credit_total    = $interest_total;
					$credit_interest = sprintf( __( 'with interest of %s%% a.m. Total: %s', 'cielo-woocommerce' ), $this->get_valid_value( $this->interest_rate ), sanitize_text_field( woocommerce_price( $interest_order_total ) ) );
				}
			}

			if ( 1 != $i && $credit_total < $smallest_value ) {
				continue;
			}

			$at_sight = ( 1 == $i ) ? 'cielo-at-sight' : '';

			if ( 'select' == $type ) {
				$html .= '<option value="' . $i . '" class="' . $at_sight . '">' . sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest ) . '</option>';
			} else {
				$html .= '<label class="' . $at_sight . '"><input type="radio" name="cielo_credit_installments" value="' . $i . '" /> ' . sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, '<strong>' . sanitize_text_field( woocommerce_price( $credit_total ) ) . '</strong>', $credit_interest ) . '</label>';
			}
		}

		if ( 'select' == $type ) {
			$html .= '</select>';
		}

		return $html;
	}

	/**
	 * Get single installment text.
	 *
	 * @param  int   $quantity
	 * @param  float $order_total
	 *
	 * @return string
	 */
	public function get_installment_text( $quantity, $order_total ) {
		$credit_total    = $order_total / $quantity;
		$credit_interest = sprintf( __( 'no interest. Total: %s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $order_total ) ) );
		$interest_rate   = $this->get_valid_value( $this->interest_rate ) / 100;

		if ( 'client' == $this->installment_type && $quantity >= $this->interest && 0 < $interest_rate ) {
			$interest_total       = $order_total * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $quantity ) ) ) );
			$interest_order_total = $interest_total * $quantity;

			if ( $credit_total < $interest_total ) {
				$credit_total    = $interest_total;
				$credit_interest = sprintf( __( 'with interest of %s%% a.m. Total: %s', 'cielo-woocommerce' ), $this->get_valid_value( $this->interest_rate ), sanitize_text_field( woocommerce_price( $interest_order_total ) ) );
			}
		}

		return sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $quantity, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest );
	}

	/**
	 * Get Checkout form field.
	 *
	 * @param  string $model
	 * @param  float  $order_total
	 */
	protected function get_checkout_form( $model = 'default', $order_total = 0 ) {

	}

	/**
	 * Payment fields.
	 *
	 * @return string
	 */
	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		// Set the payment form type.
		if ( 'webservice' == $this->store_contract ) {
			wp_enqueue_script( 'wc-credit-card-form' );

			$model = 'webservice';
		} else {
			$model = ( 'icons' == $this->design ) ? 'icons' : 'default';
		}

		// Get order total.
		if ( method_exists( $this, 'get_order_total' ) ) {
			$order_total = $this->get_order_total();
		} else {
			$order_total = $this->get_order_total();
		}

		$this->get_checkout_form( $model, $order_total );
	}

	/**
	 * Validate credit brand.
	 *
	 * @param  string $card_brand
	 *
	 * @return bool
	 */
	protected function validate_credit_brand( $card_brand ) {
		try {
			// Validate the card brand.
			if ( ! in_array( $card_brand, $this->methods ) ) {
				throw new Exception( sprintf( __( 'Please enter with a valid card brand. The following cards are accepted: %s.', 'cielo-woocommerce' ), $this->get_accepted_brands_list( $this->methods ) ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * Validate card fields.
	 *
	 * @param  array $posted
	 *
	 * @return bool
	 */
	protected function validate_card_fields( $posted ) {
		try {
			// Validate name typed for the card.
			if ( ! isset( $posted[ $this->id . '_holder_name' ] ) || '' === $posted[ $this->id . '_holder_name' ] ) {
				throw new Exception( __( 'Please type the name of the card holder.', 'cielo-woocommerce' ) );
			}

			// Validate the expiration date.
			if ( ! isset( $posted[ $this->id . '_expiry' ] ) || '' === $posted[ $this->id . '_expiry' ] ) {
				throw new Exception( __( 'Please type the card expiry date.', 'cielo-woocommerce' ) );
			}

			// Validate the cvv for the card.
			if ( ! isset( $posted[ $this->id . '_cvc' ] ) || '' === $posted[ $this->id . '_cvc' ] ) {
				throw new Exception( __( 'Please type the cvv code for the card', 'cielo-woocommerce' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * Validate installments.
	 *
	 * @param  array $posted
	 * @param  float $order_total
	 *
	 * @return bool
	 */
	protected function validate_installments( $posted, $order_total ) {
		// Stop if don't have installments.
		if ( ! isset( $posted['cielo_credit_installments'] ) && 1 == $this->installments ) {
			return true;
		}

		try {

			// Validate the installments field.
			if ( ! isset( $posted['cielo_credit_installments'] ) || '' === $posted['cielo_credit_installments'] ) {
				throw new Exception( __( 'Please select a number of installments.', 'cielo-woocommerce' ) );
			}

			$installments      = absint( $posted['cielo_credit_installments'] );
			$installment_total = $order_total / $installments;
			$_installments     = apply_filters( 'wc_cielo_max_installments', $this->installments, $order_total );
			$interest_rate     = $this->get_valid_value( $this->interest_rate ) / 100;

			if ( 'client' == $this->installment_type && $installments >= $this->interest && 0 < $interest_rate ) {
				$interest_total    = $order_total * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $installments ) ) ) );
				$installment_total = ( $installment_total < $interest_total ) ? $interest_total : $installment_total;
			}
			$smallest_value = ( 5 <= $this->smallest_installment ) ? $this->smallest_installment : 5;

			if ( $installments > $_installments || 1 != $installments && $installment_total < $smallest_value ) {
			 	throw new Exception( __( 'Invalid number of installments!', 'cielo-woocommerce' ) );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * Process webservice payment.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function process_webservice_payment( $order ) {
		return array();
	}

	/**
	 * Process buy page cielo payment.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function process_buypage_cielo_payment( $order ) {
		return array();
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( 'webservice' == $this->store_contract ) {
			return $this->process_webservice_payment( $order );
		} else {
			return $this->process_buypage_cielo_payment( $order );
		}
	}

	/**
	 * Process the order status.
	 *
	 * @param WC_Order $Order  Order data.
	 * @param int      $status Status ID.
	 * @param string   $note   Custom order note.
	 */
	public function process_order_status( $order, $status, $note = '' ) {
		$status_note = __( 'Cielo', 'cielo-woocommerce' ) . ': ' . $this->get_status_name( $status );

		// Order cancelled.
		if ( 9 == $status ) {
			$order->update_status( 'cancelled', $status_note );

			// Order failed.
		} elseif ( ( 1 != $status && 4 != $status && 6 != $status ) || -1 == $status ) {
			$order->update_status( 'failed', $status_note );

			// Order paid.
		} else {
			$order->add_order_note( $status_note . '. ' . $note );

			// Complete the payment and reduce stock levels.
			$order->payment_complete();
		}
	}

	/**
	 * Check return.
	 */
	public function check_return() {
		@ob_clean();

		if ( isset( $_GET['key'] ) && isset( $_GET['order'] ) ) {
			header( 'HTTP/1.1 200 OK' );

			$order_id = absint( $_GET['order'] );
			$order    = new WC_Order( $order_id );

			if ( $order->order_key == $_GET['key'] ) {
				do_action( 'woocommerce_' . $this->id . '_return', $order );
			}
		}

		wp_die( __( 'Invalid request', 'cielo-woocommerce' ) );
	}

	/**
	 * Return handler.
	 *
	 * @param WC_Order $order Order data.
	 */
	public function return_handler( $order ) {
		global $woocommerce;

		$tid = get_post_meta( $order->id, '_transaction_id', true );

		if ( '' != $tid ) {
			$response = $this->api->get_transaction_data( $order, $tid, $order->id . '-' . time() );

			// Set the error alert.
			if ( ! empty( $response->mensagem ) ) {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Cielo payment error: ' . print_r( $response->mensagem, true ) );
				}

				$this->helper->add_error( (string) $response->mensagem );
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
				$order_note .= $this->get_payment_method_name( (string) $payment_method->bandeira );
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
			$this->process_order_status( $order, $status, $order_note );

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
				$return_url = $this->get_return_url( $order );
			} else {
				$return_url = add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) );
			}

			// Order cancelled.
			if ( 9 == $status ) {
				$message = __( 'Order canceled successfully.', 'cielo-woocommerce' );
				if ( function_exists( 'wc_add_notice' ) ) {
					wc_add_notice( $message );
				} else {
					$woocommerce->add_message( $message );
				}

				if ( function_exists( 'wc_get_page_id' ) ) {
					$return_url = get_permalink( wc_get_page_id( 'shop' ) );
				} else {
					$return_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
				}
			}

			wp_redirect( esc_url_raw( $return_url ) );
			exit;
		} else {
			if ( function_exists( 'wc_get_page_id' ) ) {
				$cart_url = get_permalink( wc_get_page_id( 'cart' ) );
			} else {
				$cart_url = get_permalink( woocommerce_get_page_id( 'cart' ) );
			}

			wp_redirect( esc_url_raw( $cart_url ) );
			exit;
		}
	}

	/**
	 * Process a refund in WooCommerce 2.2 or later.
	 *
	 * @param  int    $order_id
	 * @param  float  $amount
	 * @param  string $reason
	 *
	 * @return bool|WP_Error True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = new WC_Order( $order_id );

		if ( ! $order || ! $order->get_transaction_id() ) {
			return false;
		}

		$diff  = ( strtotime( $order->order_date ) - strtotime( current_time( 'mysql' ) ) );
		$days  = absint( $diff / ( 60 * 60 * 24 ) );
		$limit = 120;

		if ( $limit > $days ) {
			$tid      = $order->get_transaction_id();
			$amount   = wc_format_decimal( $amount );
			$response = $this->api->do_transaction_cancellation( $order, $tid, $order->id . '-' . time(), $amount );

			// Already canceled.
			if ( ! empty( $response->mensagem ) ) {
				$order->add_order_note( __( 'Cielo', 'cielo-woocommerce' ) . ': ' . sanitize_text_field( $response->mensagem ) );

				return new WP_Error( 'cielo_refund_error', sanitize_text_field( $response->mensagem ) );
			} else {
				if ( isset( $response->cancelamentos->cancelamento ) ) {
					$order->add_order_note( sprintf( __( 'Cielo: %s - Refunded amount: %s.', 'cielo-woocommerce' ), sanitize_text_field( $response->cancelamentos->cancelamento->mensagem ), wc_price( $response->cancelamentos->cancelamento->valor / 100 ) ) );
				}

				return true;
			}
		} else {
			return new WP_Error( 'cielo_refund_error', sprintf( __( 'This transaction has been made ​​more than %s days and therefore it can not be canceled', 'cielo-woocommerce' ), $limit ) );
		}

		return false;
	}

	/**
	 * Thank you page message.
	 *
	 * @return string
	 */
	public function thankyou_page( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$order_url = $order->get_view_order_url();
		} else {
			$order_url = add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'view_order' ) ) );
		}

		if ( $order->status == 'processing' || $order->status == 'completed' ) {
			echo '<div class="woocommerce-message"><a href="' . esc_url( $order_url ) . '" class="button" style="display: block !important; visibility: visible !important;">' . __( 'View order details', 'cielo-woocommerce' ) . '</a>' . sprintf( __( 'Your payment has been received successfully.', 'cielo-woocommerce' ), woocommerce_price( $order->order_total ) ) . '<br />' . __( 'The authorization code was generated.', 'cielo-woocommerce' ) . '</div>';
		} else {
			echo '<div class="woocommerce-info">' . sprintf( __( 'For more information or questions regarding your order, go to the %s.', 'cielo-woocommerce' ), '<a href="' . esc_url( $order_url ) . '">' . __( 'order details page', 'cielo-woocommerce' ) . '</a>' ) . '</div>';
		}
	}
}
