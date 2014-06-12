<?php
/**
 * Plugin Name: Cielo WooCommerce
 * Plugin URI: http://omniwp.com.br/plugins/
 * Description: Adiciona a opção de pagamento pela Cielo ao WooCommerce
 * Author: Gabriel Reguly, omniWP, claudiosanches
 * Author URI: http://omniwp.com.br
 * Version: 3.0.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-cielo
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
	const VERSION = '3.0.0';

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
			$this->includes();

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
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
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-cielo' );

		load_textdomain( 'woocommerce-cielo', trailingslashit( WP_LANG_DIR ) . 'woocommerce-cielo/woocommerce-cielo-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-cielo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 *
	 * @return void
	 */
	private function includes() {
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
	 * WooCommerce fallback notice.
	 *
	 * @return  string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Cielo Gateway depends on the last version of the %s to work!', 'woocommerce-cielo' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-cielo' ) . '</a>' ) . '</p></div>';
	}
}

add_action( 'plugins_loaded', array( 'WC_Cielo', 'get_instance' ), 0 );

endif;
