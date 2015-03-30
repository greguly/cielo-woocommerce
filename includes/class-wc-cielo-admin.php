<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin actions.
 */
class WC_Cielo_Admin {

	/**
	 * Initialize the admin actions.
	 */
	public function __construct() {
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'cancel_payment' ), 999 );
	}

	/**
	 * Cancel order payment.
	 *
	 * @param int $order_id Order ID.
	 */
	public function cancel_payment( $order_id ) {
		$order    = new WC_Order( $order_id );
		$refunded = get_post_meta( $order_id, '_wc_cielo_transaction_refunded', true );

		if ( 'refunded' == $order->status && 'yes' != $refunded && 'cielo' == $order->payment_method ) {
			$diff   = ( strtotime( $order->order_date ) - strtotime( current_time( 'mysql' ) ) );
			$days   = absint( $diff / ( 60 * 60 * 24 ) );
			$prefix = __( 'Cielo', 'cielo-woocommerce' ) . ': ';

			if ( 90 > $days ) {
				$tid = get_post_meta( $order->id, '_transaction_id', true );

				// Backward compatibility.
				if ( ! $tid ) {
					$tid = get_post_meta( $order->id, '_wc_cielo_transaction_tid', true );
				}

				$gateway  = new WC_Cielo_Gateway();
				$response = $gateway->api->do_transaction_cancellation( $order, $tid, $order->id . '-' . time() );

				// Already canceled.
				if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
					$order->add_order_note( $prefix . sanitize_text_field( $response->mensagem ) );
				} else {
					$order->add_order_note( $prefix . __( 'Transaction canceled successfully', 'cielo-woocommerce' ) );
				}

			} else {
				$order->add_order_note( $prefix . __( 'This transaction has been made more than 90 days and therefore it can not be canceled', 'cielo-woocommerce' ) );
			}

			update_post_meta( $order_id, '_wc_cielo_transaction_refunded', 'yes' );
		}
	}
}

new WC_Cielo_Admin();
