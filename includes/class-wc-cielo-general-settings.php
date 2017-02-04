<?php
/**
 * WC Cielo General Settings Class.
 *
 * Built the Cielo General Settings methods.
 */
class WC_Cielo_General_Settings_Gateway extends WC_Payment_Gateway {

	/**
	 * Cielo WooCommerce API.
	 *
	 * @var WC_Cielo_API
	 */
	public $api_version = null;

	/**
	 * Gateway actions.
	 */
	public function __construct() {
		$this->id           = 'cielo_general_settings';
		$this->icon         = apply_filters( 'wc_cielo_general_settings_icon', '' );
		$this->has_fields   = true;
		$this->method_title = __( 'Cielo - General Settings', 'cielo-woocommerce' );
        $this->supports     = array( 'products', 'refunds' );

        // Load the form fields.
		$this->init_form_fields();

		// Define user set variables.
		$this->api_version = $this->get_option( 'api_version' );

        // Actions.
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
        // Get version list from json file
        include_once dirname( __FILE__ ) . '/api/class-wc-cielo-version.php';

        $this->form_fields = array(
			'api_version' => array(
				'title'       => __( 'API Version', 'cielo-woocommerce' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Select the API Version', 'cielo-woocommerce' ),
				'desc_tip'    => true,
				'default'     => WC_Cielo_Version::getDefaultVersion(),
				'options'     => WC_Cielo_Version::getVersion('description'),
            ),
        );
        
    }


}
