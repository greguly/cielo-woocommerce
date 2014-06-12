<?php
/**
 * WC Cielo API Class.
 */
class WC_Cielo_API {

	/**
	 * Gateway class.
	 *
	 * @var WC_Cielo_Gateway
	 */
	protected $gateway;

	/**
	 * Smallest Installment
	 *
	 * @var int
	 */
	protected static $smallest_installment = 5;

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
			'visa'       => __( 'Visa', 'woocommerce-cielo' ),
			'mastercard' => __( 'MasterCard', 'woocommerce-cielo' ),
			'diners'     => __( 'Diners', 'woocommerce-cielo' ),
			'discover'   => __( 'Discover', 'woocommerce-cielo' ),
			'elo'        => __( 'Elo', 'woocommerce-cielo' ),
			'amex'       => __( 'American Express', 'woocommerce-cielo' )
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

	public static function get_credit_methods() {
		return array( 'visa', 'mastercard', 'diners', 'discover', 'elo', 'amex' );
	}

	public static function get_store_credit_methods() {
		return array( 'visa', 'mastercard', 'diners', 'elo', 'amex' );
	}

	public static function get_credit_cards() {
		return array( 'visa', 'mastercard', 'diners', 'elo', 'amex' );
	}

	public static function get_debit_methods() {
		return array( 'visa' );
	}

	public static function get_smallest_installment() {
		return self::$smallest_installment;
	}
}
