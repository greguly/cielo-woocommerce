<?php
/**
 * Plugin Name: Cielo WooCommerce - Solução Webservice
 * Plugin URI:  http://omniwp.com.br/plugins/
 * Description: Works using the Cielo Webservice Solution to receive payments on WooCommerce.
 * Author:      Gabriel Reguly, Claudio Sanches, Paulo Vieira
 * Author URI:  http://omniwp.com.br
 * Version:     4.0.14
 * License:     GPLv2 or later
 * Text Domain: cielo-woocommerce
 * Domain Path: /languages
 *
 * Cielo WooCommerce - Solução Webservice is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Cielo WooCommerce - Solução Webservice is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Cielo WooCommerce - Solução Webservice. If not, see
 * <https://www.gnu.org/licenses/gpl-2.0.txt>.
 *
 * @package WC_Cielo
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
		const VERSION = '4.0.14';

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
			// Load plugin text domain.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Checks with WooCommerce and WooCommerce is installed.
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				$this->upgrade();
				$this->includes();

				// Add the gateway.
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

				// Admin actions.
				if ( is_admin() ) {
					add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
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
		 * Get templates path.
		 *
		 * @return string
		 */
		public static function get_templates_path() {
			return plugin_dir_path( __FILE__ ) . 'templates/';
		}

		/**
		 * Load the plugin text domain for translation.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'cielo-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Includes.
		 */
		private function includes() {
			include_once dirname( __FILE__ ) . '/includes/class-wc-cielo-xml.php';
			include_once dirname( __FILE__ ) . '/includes/class-wc-cielo-helper.php';
			include_once dirname( __FILE__ ) . '/includes/class-wc-cielo-api.php';
			include_once dirname( __FILE__ ) . '/includes/class-wc-cielo-debit-gateway.php';
			include_once dirname( __FILE__ ) . '/includes/class-wc-cielo-credit-gateway.php';
		}

		/**
		 * Add the gateway to WooCommerce.
		 *
		 * @param   array $methods WooCommerce payment methods.
		 *
		 * @return  array          Payment methods with Cielo.
		 */
		public function add_gateway( $methods ) {
			array_push( $methods, 'WC_Cielo_Debit_Gateway', 'WC_Cielo_Credit_Gateway' );

			return $methods;
		}

		/**
		 * Upgrade plugin options.
		 */
		private function upgrade() {
			if ( is_admin() ) {
				$version = get_option( 'wc_cielo_version', '0' );

				if ( version_compare( $version, WC_Cielo::VERSION, '<' ) ) {

					// Upgrade from 3.x.
					if ( $options = get_option( 'woocommerce_cielo_settings' ) ) {
						// Credit.
						$credit_options = array(
						'enabled'              => $options['enabled'],
						'title'                => __( 'Credit Card', 'cielo-woocommerce' ),
						'description'          => $options['description'],
						'store_contract'       => 'buypage_cielo',
						'environment'          => $options['environment'],
						'number'               => $options['number'],
						'key'                  => $options['key'],
						'methods'              => $options['methods'],
						'authorization'        => $options['authorization'],
						'smallest_installment' => $options['smallest_installment'],
						'interest_rate'        => $options['interest_rate'],
						'installments'         => $options['installments'],
						'interest'             => $options['interest'],
						'installment_type'     => $options['installment_type'],
						'design_options'       => $options['design_options'],
						'design'               => $options['design'],
						'debug'                => $options['debug'],
						);

						// Debit.
						$debit_methods = array();
						if ( 'mastercard' == $options['debit_methods'] ) {
							$debit_methods = array( 'maestro' );
						} else if ( 'all' == $options['debit_methods'] ) {
							$debit_methods = array( 'visaelectron', 'maestro' );
						} else {
							$debit_methods = array( 'visaelectron' );
						}

						$debit_options  = array(
						'enabled'        => ( 'none' == $options['debit_methods'] ) ? 'no' : $options['enabled'],
						'title'          => __( 'Debit Card', 'cielo-woocommerce' ),
						'description'    => $options['description'],
						'store_contract' => 'buypage_cielo',
						'environment'    => $options['environment'],
						'number'         => $options['number'],
						'key'            => $options['key'],
						'methods'        => $debit_methods,
						'authorization'  => $options['authorization'],
						'debit_discount' => $options['debit_discount'],
						'design_options' => $options['design_options'],
						'design'         => $options['design'],
						'debug'          => $options['debug'],
						);

						// Save the new options.
						update_option( 'woocommerce_cielo_credit_settings', $credit_options );
						update_option( 'woocommerce_cielo_debit_settings', $debit_options );

						// Delete old options.
						delete_option( 'woocommerce_cielo_settings' );
					}

					update_option( 'wc_cielo_version', WC_Cielo::VERSION );
				}
			}
		}

		/**
		 * Register scripts.
		 */
		public function register_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Styles.
			wp_register_style( 'wc-cielo-checkout-icons', plugins_url( 'assets/css/checkout-icons' . $suffix . '.css', __FILE__ ), array(), WC_Cielo::VERSION );
			wp_register_style( 'wc-cielo-checkout-webservice', plugins_url( 'assets/css/checkout-webservice' . $suffix . '.css', __FILE__ ), array(), WC_Cielo::VERSION );
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function woocommerce_missing_notice() {
			include_once dirname( __FILE__ ) . '/includes/views/notices/html-notice-woocommerce-missing.php';
		}

		/**
		 * Action links.
		 *
		 * @param  array $links
		 *
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array();

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
				$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cielo_credit' ) ) . '">' . __( 'Credit Card Settings', 'cielo-woocommerce' ) . '</a>';
				$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cielo_debit' ) ) . '">' . __( 'Debit Card Settings', 'cielo-woocommerce' ) . '</a>';
			} else {
				$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_cielo_credit_gateway' ) ) . '">' . __( 'Credit Card Settings', 'cielo-woocommerce' ) . '</a>';
				$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_cielo_debit_gateway' ) ) . '">' . __( 'Debit Card Settings', 'cielo-woocommerce' ) . '</a>';
			}

			return array_merge( $plugin_links, $links );
		}
	}

	add_action( 'plugins_loaded', array( 'WC_Cielo', 'get_instance' ), 0 );

endif;
