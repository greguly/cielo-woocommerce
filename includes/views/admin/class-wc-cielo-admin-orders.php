<?php
use Cielo\API30\Ecommerce\Request\CieloRequestException;
/**
 * Admin orders actions.
 *
 * @package WooCommerce_Cielo/Admin/Orders
 * @since   4.0.0
 * @version 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Cielo orders.
 */
class WC_Cielo_Admin_Orders {


    /**
     * Initialize the order actions.
     */
    public function __construct( ) {
        add_action('add_meta_boxes', array($this, 'register_metabox'));
        add_action('woocommerce_process_shop_order_meta', array($this, 'capture_sale'));
    }

    /**
     * Register tracking code metabox.
     */
    public function register_metabox( ) {

        global $post;
        $order = wc_get_order( $post->ID );

        if ( $order->payment_method == 'cielo_credit' ) {
            add_meta_box(
                'wc_cielo',
                'Cielo - Capture Sale',
                array($this, 'metabox_content'),
                'shop_order',
                'side',
                'default'
            );
        }

    }

    /**
     * Tracking code metabox content.
     *
     * @param WC_Post $post Post data.
     */
    public function metabox_content( $post ) {

        $order     = wc_get_order( $post->ID );

        $diff  = ( strtotime( $order->order_date ) - strtotime( current_time( 'mysql' ) ) );
        $days  = absint( $diff / ( 60 * 60 * 24 ) );
        $limit = 15;

        echo '<label style="display: '. ( get_post_meta( $post->ID, '_cielo_sale_captured_status', true ) == "yes" ? 'none' : 'block' ) .';">'. sprintf( __( '%s day(s) left', 'cielo-woocommerce' ), ($limit - $days) ) .'</label>';
        echo '<ul class="cielo_action submitbox" style="display: flex;">' .
             '   <li class="wide" id="actions" style="width: 100%">' .
             '       <input type="text" id="capture_amount" name="capture_amount" value="' . esc_attr( wc_format_localized_price( $order->get_total() ) ) . '"  class="wc_input_price regular_price" ' . disabled( get_post_meta( $post->ID, '_cielo_sale_captured_status', true ), "yes", false ) . ' />'.
             '   </li>' .
             '   <li class="wide">' .
             '       <input type="submit" class="button tips" id="cielo_sale_captured" name="cielo_sale_captured" value="' . ( ( get_post_meta( $post->ID, '_cielo_sale_captured_status', true ) == "yes" ) ? "Captured" : "Capture" ) . '" data-tip="'. __( "Capture sale to finalize and charge transaction", "woocommerce" ) . '" ' . disabled( get_post_meta( $post->ID, '_cielo_sale_captured_status', true ), "yes", false ) . ' />' .
             '   </li>' .
             '</ul>';

    }

    /**
     * Save tracking code.
     *
     * @param int $post_id Current post type ID.
     */
    public function capture_sale( $post_id ) {

        if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
            return;
        }

        if ( isset( $_POST['cielo_sale_captured'] ) ) {

//            $tracking_code = sanitize_text_field( $tracking_code );

            $order     = wc_get_order( $post_id );
            echo str_replace('"', '', $order->get_transaction_id());

            echo 'Order Date: ' . $order->order_date;
            
            $cielo_credit = new WC_Cielo_Credit_Gateway();

            $api = new WC_Cielo_API( $cielo_credit );

            try {

                $response = $api->do_sale_capture( $order, '', $order->get_transaction_id(), $_POST['cielo_sale_captured'] );

            } catch (CieloRequestException $e) {
                $error = $e->getCieloError();
            }

            // Check if has a error.
            if ( $response['error'] ) {
                // Add a message to order notes
                $order->add_order_note( __( 'Cielo', 'cielo-woocommerce' ) . ': ' . sanitize_text_field( $response['message'] ) );
                // Send a error message to Wordpress
                return new WP_Error( 'cielo_capture_error', sanitize_text_field( $response['message'] ) );
            } else {
                // Add a message to order notes
                $order->add_order_note(  sprintf( __( 'Cielo: %s - Captured amount: %s.', 'cielo-woocommerce' ), $order->get_transaction_id(), $_POST['capture_amount'] ) );
                // Update field when successfully updated
                update_post_meta($post_id, '_cielo_sale_captured_status', 'yes');

                return true;
            }


        }

    }

}

new WC_Cielo_Admin_Orders();