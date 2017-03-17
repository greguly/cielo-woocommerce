<?php
/**
 * WC Cielo General Settings Class.
 *
 * Built the Cielo General Settings methods.
 */
class WC_Cielo_General_Settings_Gateway extends WC_Payment_Gateway {

	/**
	 * Cielo WooCommerce Default value Version.
	 *
	 */
	private $default = null;

	/**
	 * Cielo WooCommerce Options value Version.
	 *
	 */
    private $options = null;

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

        // Load the form fields.
        $this->init_form_fields();

        // Define user set variables.
        $this->api_version        = $this->get_option( 'api_version' );
        $this->admin_sale_capture = $this->get_option( 'admin_sale_capture' );
        $this->time_sale_capture  = $this->get_option( 'time_sale_capture' );

        // Actions.
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
        $this->form_fields = array(
			'api_version' => array(
				'title'       => __( 'API Version', 'cielo-woocommerce' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Select the API Version', 'cielo-woocommerce' ),
				'desc_tip'    => true,
                'default'     => 'version_1_5',
                'options'     => array(
                    'version_1_5' => __( 'Version 1.5', 'cielo-woocommerce' ),
                    'version_3_0' => __( 'Version 3.0', 'cielo-woocommerce' ),
                ),
//                'default'     => $this->default,
//                'options'     => $this->options,
            ),
            'admin_sale_capture' => array(
                'title'   => __( 'Enable/Disable', 'cielo-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Sale Capture in Admin Order Page', 'cielo-woocommerce' ),
                'description' => __( 'Enable manual capture in Admin Order Page', 'cielo-woocommerce' ),
                'desc_tip'    => true,
                'default' => 'no',
            ),
            'time_sale_capture' => array(
                'title'   => __( 'Term to Capture', 'cielo-woocommerce' ),
                'type'    => 'text',
                'label'   => __( 'Term in days to Sale Capture in Admin Order Page', 'cielo-woocommerce' ),
                'description' => __( 'Term in days before exceed Sale Capture time', 'cielo-woocommerce' ),
                'desc_tip' => true,
                'default' => 5,
            ),
        );

    }

	/**
	 * Admin page.
	 */
	public function admin_options() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'wc-cielo-admin', plugins_url( 'assets/js/admin/admin' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Cielo::VERSION, true );

		include dirname( __FILE__ ) . '/views/html-admin-page.php';

	}

}
