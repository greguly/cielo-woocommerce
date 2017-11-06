<?php
/**
 * Created by PhpStorm.
 * User: Sander
 * Date: 04/11/2017
 * Time: 18:59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Cielo_Credit_Installment_Change {

    /**
     * Initialize actions.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Init autofill.
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
        //add_action( 'wc_ajax_' . $this->ajax_endpoint, array( $this, 'installments_change' ) );
        add_action( 'woocommerce_cart_calculate_fees', array( $this, 'cart_calculate_fees' ) );
    }

    /**
     * Frontend scripts.
     */
    public function frontend_scripts() {
        if ( is_checkout() || is_account_page() ) {
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            //$suffix = '';

            wp_enqueue_script( 'wc-cielo-credit-installment-change', plugins_url( 'assets/js/credit-card/checkout-installment-change' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'jquery-blockui' ), WC_Cielo::VERSION, true );

            wp_localize_script(
                'wc-cielo-credit-installment-change',
                'WCCieloCreditInstallmentChange',
                array(
                    'url'   => WC_AJAX::get_endpoint( $this->ajax_endpoint ),
                    'discount_nonce' => wp_create_nonce( 'woocommerce_cielo_credit_installment_change_nonce' ),
                )
            );
        }
    }

    public function cart_calculate_fees( $cart ) {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) || is_cart() ) {
            return;
        }

        if ( isset( $_POST['post_data'] ) ) {
            parse_str( $_POST['post_data'], $post_data );
        } else {
            $post_data = $_POST; // fallback for final checkout (non-ajax)
        }

        if (isset($post_data['cielo_credit_installments'])) {

            $settings = get_option('woocommerce_' . WC()->session->chosen_payment_method . '_settings');

            $cielo_credit_installments = (int) $post_data['cielo_credit_installments'];
            $interest = $settings['interest'];

            if ($cielo_credit_installments == 1) {

                if (isset($settings['credit_discount_x1'])) {

                    // Gets the gateway discount.
                    $credit_discount_x1 = $settings['credit_discount_x1'];

                    $total = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total );

                    $discount = $total * ($credit_discount_x1 / 100);
                    $cart->add_fee(
                        sprintf(__('With discount of %s at sight credit card', 'cielo-woocommerce'), $credit_discount_x1 . '%', sanitize_text_field(woocommerce_price( $total ))),
                        -$discount
                    );

                }

            } elseif ('store' == $settings['installment_type'] && $cielo_credit_installments >= $interest && 0 < $settings['interest_rate'] ) {

                $total = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total );

                $interest_rate = (float) $settings['interest_rate'];

                $interest_subtotal = $total / $cielo_credit_installments;

                if ( $cielo_credit_installments < $settings['installments'] ) {
                    $interest_total = $interest_subtotal * ( ( ( $interest_rate / 100 ) + 1 ) - 0.02 );
                } else {
                    $interest_total = $interest_subtotal * ( ( ( $interest_rate / 100 ) + 1 ) + 0.02 );
                }

                $addition = ($interest_total * $cielo_credit_installments) - $total;

                $cart->add_fee(
                    sprintf( __( 'With addition of about %s per interest a.m.', 'cielo-woocommerce' ), sanitize_text_field(woocommerce_price( $interest_total - $interest_subtotal ) ) ),
                    $addition
                );

            }

        }

    }

}

new WC_Cielo_Credit_Installment_Change();