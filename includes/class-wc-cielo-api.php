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
	public $gateway;

	/**
	 * Constructor.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}
}
