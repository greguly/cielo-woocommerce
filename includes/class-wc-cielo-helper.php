<?php
/**
 * WC Cielo Helper Class.
 */
class WC_Cielo_Helper {

	/**
	 * Gateway class.
	 *
	 * @var WC_Cielo_Gateway
	 */
	protected $gateway;

	/**
	 * Constructor.
	 *
	 * @param WC_Cielo_Gateway $gateway
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
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
			'amex'       => __( 'American Express', 'cielo-woocommerce' ),
			'jcb'        => __( 'JCB', 'cielo-woocommerce' ),
			'aura'       => __( 'Aura', 'cielo-woocommerce' ),
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

	/**
	 * Get the accepted brands in a text list.
	 *
	 * @param  array $methods
	 *
	 * @return string
	 */
	public static function get_accepted_brands_list( $methods ) {
		$total = count( $methods );
		$count = 1;
		$list  = '';

		foreach ( $methods as $method ) {
			$name = self::get_payment_method_name( $method );

			if ( $count == ( $total - 1 ) ) {
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
	 * Get debit methods.
	 *
	 * @return array
	 */
	public static function get_debit_methods( $debit_methods ) {
		switch ( $debit_methods ) {
			case 'all' :
				$methods = array( 'visa', 'mastercard' );
				break;
			case 'visa' :
				$methods = array( 'visa' );
				break;
			case 'mastercard' :
				$methods = array( 'mastercard' );
				break;

			default :
				$methods = array();
				break;
		}

		return $methods;
	}

	/**
	 * Get methods who accepts authorization.
	 *
	 * @return array
	 */
	public static function get_accept_authorization() {
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
	public static function get_valid_value( $value ) {
		$value = str_replace( '%', '', $value );
		$value = str_replace( ',', '.', $value );

		return $value;
	}

	/**
	 * Get the order return URL.
	 *
	 * @param  WC_Order $order Order data.
	 *
	 * @return string
	 */
	public static function get_return_url( $order ) {
		global $woocommerce;

		// Backwards compatibility with WooCommerce version prior to 2.1.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$url = WC()->api_request_url( get_class( $this->gateway ) );
		} else {
			$url = $woocommerce->api_request_url( get_class( $this->gateway ) );
		}

		return urlencode( htmlentities( add_query_arg( array( 'key' => $order->order_key, 'order' => $order->id ), $url ), ENT_QUOTES ) );
	}

	/**
	 * Get the status name.
	 *
	 * @param  int $id Status ID.
	 *
	 * @return string
	 */
	public static function get_status_name( $id ) {
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
	public static function get_order_total() {
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
	 * Add error messages in checkout.
	 *
	 * @param string $message Error message.
	 */
	public function add_error( $message ) {
		global $woocommerce;

		$title = '<strong>' . esc_attr( $this->gateway->title ) . ':</strong> ';

		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $title . $message, 'error' );
		} else {
			$woocommerce->add_error( $title . $message );
		}
	}

	/**
	 * Get installments HTML.
	 *
	 * @param  string $type        'select' or 'radio'.
	 * @param  float  $order_total Order total.
	 *
	 * @return string
	 */
	public function get_installments_html( $type = 'select', $order_total = 0 ) {
		$html = '';

		if ( 'select' == $type ) {
			$html .= '<select id="cielo-installments" name="cielo_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">';
		}

		$debit_methods   = self::get_debit_methods( $this->gateway->debit_methods );
		$available_debit = array_intersect( $debit_methods, $this->gateway->methods );

		if ( ! empty( $available_debit ) ) {
			$debit_total    = $cart_total * ( ( 100 - self::get_valid_value( $this->gateway->debit_discount ) ) / 100 );
			$debit_discount = ( $cart_total > $debit_total ) ? ' (' . self::get_valid_value( $this->gateway->debit_discount ) . '% ' . _x( 'off', 'price', 'cielo-woocommerce' ) . ')' : '';

			if ( 'select' == $type ) {
				$html .= '<option value="0" class="cielo-debit" data-debit="' . esc_attr( $this->gateway->debit_methods ) . '">' . sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), sanitize_text_field( woocommerce_price( $debit_total ) ), $debit_discount ) . '</option>';
			} else {
				$html .= '<label class="cielo-debit" data-debit="' . esc_attr( $this->gateway->debit_methods ) . '"><input type="radio" name="cielo_installments" value="0" /> ' . sprintf( __( 'Debit %s%s', 'cielo-woocommerce' ), '<strong>' . sanitize_text_field( woocommerce_price( $debit_total ) ) . '</strong>', $debit_discount ) . '</label>';
			}
		}

		for ( $i = 1; $i <= $this->gateway->installments; $i++ ) {

			$interest_rate   = self::get_valid_value( $this->gateway->interest_rate ) / 100;
			$financial_index = $interest_rate / (1 - ( 1 / pow( 1 + $interest_rate, $i ) ) );
			$credit_total    = $cart_total / $i;
			$credit_interest = sprintf(__( 'no interest Total: %s', 'cielo-woocommerce' ),sanitize_text_field( woocommerce_price( $cart_total ) ));
			$smallest_value  = ( 5 <= $this->gateway->smallest_installment ) ? $this->gateway->smallest_installment : 5;

			if ( 'client' == $this->gateway->installment_type && $i >= $this->gateway->interest ) {
				$interest_total = $cart_total * $financial_index;
				$interest_cart_total = $interest_total*$i;

				if ( $credit_total < $interest_total ) {
					$credit_total    = $interest_total;
					$credit_interest = sprintf(__( 'with interest of %s%% a.m. Total: %s', 'cielo-woocommerce' ), self::get_valid_value( $this->gateway->interest_rate ), sanitize_text_field( woocommerce_price( $interest_cart_total ) ) );
				}
			}

			if ( 1 != $i && $credit_total < $smallest_value ) {
				continue;
			}

			$at_sight = ( 1 == $i ) ? 'cielo-at-sight' : '';

			if ( 'select' == $type ) {
				$html .= '<option value="' . $i . '" class="' . $at_sight . '">' . sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, sanitize_text_field( woocommerce_price( $credit_total ) ), $credit_interest ) . '</option>';
			} else {
				$html .= '<label class="' . $at_sight . '"><input type="radio" name="cielo_installments" value="' . $i . '" /> ' . sprintf( __( '%sx of %s %s', 'cielo-woocommerce' ), $i, '<strong>' . sanitize_text_field( woocommerce_price( $credit_total ) ) . '</strong>', $credit_interest ) . '</label>';
			}
		}

		if ( 'select' == $type ) {
			$html .= '</select>';
		}

		return $html;
	}

	/**
	 * Process the order status.
	 *
	 * @param WC_Order $Order  Order data.
	 * @param int      $status Status ID.
	 * @param string   $note   Custom order note.
	 */
	public function process_order_status( $order, $status, $note = '' ) {
		$status_note = __( 'Cielo', 'cielo-woocommerce' ) . ': ' . self::get_status_name( $status );

		// Order cancelled.
		if ( 9 == $status ) {
			$order->update_status( 'cancelled', $status_note );

		// Order failed.
		} elseif ( ( 4 != $status && 6 != $status ) || -1 == $status ) {
			$order->update_status( 'failed', $status_note );

		// Order paid.
		} else {
			$order->add_order_note( $status_note . '. ' . $note );

			// Complete the payment and reduce stock levels.
			$order->payment_complete();
		}
	}

	/**
	 * Process a refund in WooCommerce 2.2 or later.
	 *
	 * @param  int    $order_id
	 * @param  float  $amount
	 * @param  string $reason
	 *
	 * @return bool|wp_error True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = new WC_Order( $order_id );

		if ( ! $order || ! $order->get_transaction_id() ) {
			return false;
		}

		$diff = ( strtotime( $order->order_date ) - strtotime( current_time( 'mysql' ) ) );
		$days = absint( $diff / ( 60 * 60 * 24 ) );

		if ( 90 > $days ) {
			$tid      = $order->get_transaction_id();
			$gateway  = new WC_Cielo_Gateway();
			$amount   = number_format( wc_format_decimal( $amount ), 2, '', '' );
			$response = $this->gateway->api->do_transaction_cancellation( $order, $tid, $order->id . '-' . time(), $amount );

			// Already canceled.
			if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
				$order->add_order_note( __( 'Cielo', 'cielo-woocommerce' ) . ': ' . sanitize_text_field( $response->mensagem ) );

				return new WP_Error( 'cielo_refund_error', sanitize_text_field( $response->mensagem ) );
			} else {
				if ( isset( $response->cancelamentos->cancelamento ) ) {
					$order->add_order_note( sprintf( __( 'Cielo: %s - Refunded amount: %s.', 'cielo-woocommerce' ), sanitize_text_field( $response->cancelamentos->cancelamento->mensagem ), wc_price( $response->cancelamentos->cancelamento->valor / 100 ) ) );
				}

				return true;
			}

		} else {
			return new WP_Error( 'cielo_refund_error', __( 'This transaction has been made ​​more than 90 days and therefore it can not be canceled', 'cielo-woocommerce' ) );
		}

		return false;
	}
}
