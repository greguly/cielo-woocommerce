<?php
/**
 * Plugin Name: Cielo WooCommerce
 * Plugin URI: http://omniwp.com.br/plugins/
 * Description: Adiciona a opção de pagamento pela Cielo ao WooCommerce
 * Author: Gabriel Reguly, claudiosanches
 * Author URI: http://omniwp.com.br
 * Version: 3.0.3
 * License: GPLv2 or later
 * Text Domain: cielo-woocommerce
 * Domain Path: /languages/
 * Copyright: © 2012, 2013, 2014 omniWP
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Cielo' ) ) :

/**
 * WooCommerce WC_Cielo main class.
 */
class WC_Cielo {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '3.0.3';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin public actions.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce and WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			$this->upgrade();
			$this->includes();

			// Add the gateway.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

			// Admin actions.
			if ( is_admin() ) {
				add_action( 'woocommerce_process_shop_order_meta', array( $this, 'cancel_payment' ), 999 );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'cielo-woocommerce' );

		load_textdomain( 'cielo-woocommerce', trailingslashit( WP_LANG_DIR ) . 'cielo-woocommerce/cielo-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( 'cielo-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 *
	 * @return void
	 */
	private function includes() {
		include_once( 'includes/class-wc-cielo-xml.php' );
		include_once( 'includes/class-wc-cielo-api.php' );
		include_once( 'includes/class-wc-cielo-gateway.php' );
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param   array $methods WooCommerce payment methods.
	 *
	 * @return  array          Payment methods with Cielo.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Cielo_Gateway';

		return $methods;
	}

	/**
	 * Cancel order payment.
	 *
	 * @param  int $order_id Order ID.
	 *
	 * @return void
	 */
	public function cancel_payment( $order_id ) {
		$order    = new WC_Order( $order_id );
		$refunded = get_post_meta( $order_id, '_wc_cielo_transaction_refunded', true );

		if ( 'refunded' == $order->status && 'yes' != $refunded && 'cielo' == $order->payment_method ) {
			$diff   = ( strtotime( $order->order_date ) - strtotime( current_time( 'mysql' ) ) );
			$days   = absint( $diff / ( 60 * 60 * 24 ) );
			$prefix = __( 'Cielo', 'cielo-woocommerce' ) . ': ';

			if ( 90 > $days ) {
				$tid      = get_post_meta( $order->id, '_wc_cielo_transaction_tid', true );
				$gateway  = new WC_Cielo_Gateway();
				$response = $gateway->api->do_transaction_cancellation( $order, $tid, $order->id . '-' . time() );

				// Already canceled.
				if ( isset( $response->mensagem ) && ! empty( $response->mensagem ) ) {
					$order->add_order_note( $prefix . sanitize_text_field( $response->mensagem ) );
				} else {
					$order->add_order_note( $prefix . __( 'Transaction canceled successfully', 'cielo-woocommerce' ) );
				}

			} else {
				$order->add_order_note( $prefix . __( 'This transaction has been made ​​more than 90 days and therefore it can not be canceled', 'cielo-woocommerce' ) );
			}

			update_post_meta( $order_id, '_wc_cielo_transaction_refunded', 'yes' );
		}
	}

	/**
	 * Upgrade plugin options.
	 *
	 * @return void
	 */
	private function upgrade() {
		if ( is_admin() ) {
			$version = get_option( 'wc_cielo_version', '0' );

			if ( version_compare( $version, WC_Cielo::VERSION, '<' ) ) {

				$options     = get_option( 'woocommerce_cielo_settings' );
				$new_options = array();

				// Upgrade from 2.0.x.
				if ( isset( $options['mode'] ) ) {
					$new_options['enabled'] = $options['enabled'];
					$new_options['title'] = $options['title'];
					$new_options['description'] = $options['description'];
					$new_options['environment'] = $options['mode'];
					$new_options['number'] = $options['numero'];
					$new_options['key'] = $options['chave'];
					$new_options['methods'] = $options['meios'];
					$new_options['authorization'] = $options['autorizar'];
					$new_options['smallest_installment'] = $options['parcela_minima'];
					$new_options['interest_rate'] = $options['taxa_juros'];
					$new_options['interest_rate'] = $options['debit_discount'];
					$new_options['installments'] = $options['parcelas'];
					$new_options['interest'] = $options['juros'];
					$new_options['installment_type'] = ( '2' == $options['parcelamento'] ) ? 'client' : 'store';
					$new_options['design'] = 'default';
				} else {
					$new_options = $options;
				}

				update_option( 'woocommerce_cielo_settings', $new_options );
				update_option( 'wc_cielo_version', WC_Cielo::VERSION );
			}
		}
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return  string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Cielo Gateway depends on the last version of the %s to work!', 'cielo-woocommerce' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'cielo-woocommerce' ) . '</a>' ) . '</p></div>';
	}
}

add_action( 'plugins_loaded', array( 'WC_Cielo', 'get_instance' ), 0 );

endif;
