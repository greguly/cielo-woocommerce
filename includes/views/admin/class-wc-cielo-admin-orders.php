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
     * Register capture sale metabox.
     */
    public function register_metabox( ) {

        global $post;
        $order = wc_get_order( $post->ID );

        if (isset($order)) {
            if (is_array($order)) {
                if ($order->payment_method == 'cielo_credit') {
                    add_meta_box(
                        'wc_cielo',
                        __('Cielo - Capture Sale', 'cielo-woocommerce'),
                        array($this, 'metabox_content'),
                        'shop_order',
                        'side',
                        'default'
                    );
                }
            }
        }

    }

    /**
     * Capture sale metabox content.
     *
     * @param WC_Post $post Post data.
     */
    public function metabox_content( $post ) {
        // Order
        $order     = wc_get_order( $post->ID );

        // Difference between date of order and present days
        $diff  = ( strtotime( $order->order_date ) - strtotime( current_time( 'mysql' ) ) );
        $days  = absint( $diff / ( 60 * 60 * 24 ) );
        $limit = maybe_unserialize(get_option('woocommerce_cielo_general_settings_settings'))['time_sale_capture'];

        $captured_status = get_post_meta($post->ID, '_cielo_sale_captured_status', true);

        if ($captured_status != "yes") {

            if ($limit > $days) {

                echo '<label>' . sprintf(__('%s day(s) left', 'cielo-woocommerce'), ($limit - $days)) . '</label>';
                echo '<ul class="cielo_action submitbox" style="display: flex;">' .
                    '   <li class="wide" id="actions" style="width: 100%">' .
                    '       <input type="number" step="0.01" min="0" max="' . esc_attr($order->get_total()) . '" id="capture_amount" name="capture_amount" value="' . esc_attr($order->get_total()) . '"  class="wc_input_price regular_price" ' . disabled($captured_status, "yes", false) . ' style="width: 90%" />' .
                    '   </li>' .
                    '   <li class="wide">' .
                    '       <input type="submit" class="button tips" id="cielo_sale_captured" name="cielo_sale_captured" onclick="return confirm(\''. __('Do you right about proceed with that capture? This operation cannot be undone.', 'cielo-woocommerce') . '\')" value="' . ((get_post_meta($post->ID, '_cielo_sale_captured_status', true) == "yes") ? __("Captured", "cielo-woocommerce") : __("Capture", "cielo-woocommerce")) . '" data-tip="' . __("Capture sale to finalize and charge transaction", "cielo-woocommerce") . '" ' . disabled($captured_status, "yes", false) . ' />' .
                    '   </li>' .
                    '</ul>';

            } else {

                echo '<label>' . __('Term to capture sale exceeded', 'cielo-woocommerce') . '</label>';

            }

        } else {
            echo '<label>' . __('Captured', 'cielo-woocommerce') . '</label>';
            echo
                '<div>' .
                '    <input type="number" step="0.01" min="0" max="' . esc_attr($order->get_total()) . '" id="capture_amount" name="capture_amount" value="' . $order->get_total() . '"  class="wc_input_price regular_price" style="width: 100%" disabled />' .
                '</div>';

        }

    }

    /**
     * Do action capture sale.
     *
     * @param int $post_id Current post type ID.
     */
    public function capture_sale( $post_id ) {

        if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
            return;
        }

        // VErify if have a value
        if ( isset( $_POST['capture_amount'] ) ) {

            //Get order
            $order = wc_get_order( $post_id );

            //Create a instance of Credit Gateway
            $cielo_credit = new WC_Cielo_Credit_Gateway();

            //Create a instance of Credit API
            $api = new WC_Cielo_API( $cielo_credit );

            //Sale Capture
            try {
                $response = $api->do_sale_capture( $order, '', $order->get_transaction_id(), esc_attr($_POST ['capture_amount'] ) );
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