<?php
/**
 * Extends the SimpleXMLElement.
 */
class WC_Cielo_XML extends SimpleXMLElement {

	/**
	 * Add CDATA.
	 *
	 * @param string $string Some string.
	 */
	public function add_cdata( $string ) {
		$node = dom_import_simplexml( $this );
		$no   = $node->ownerDocument;

		$node->appendChild( $no->createCDATASection( trim( $string ) ) );
	}

	/**
	 * Add account data.
	 *
	 * @param string $number Cielo account number.
	 * @param string $key    Cielo account key.
	 */
	public function add_account_data( $number, $key ) {
		$account_data = $this->addChild( 'dados-ec' );
		$account_data->addChild( 'numero', $number );
		$account_data->addChild( 'chave', $key );
	}

	/**
	 * Add order data.
	 *
	 * @param WC_Order $order           WooCommerce order data.
	 * @param float    $total           Order total.
	 * @param int      $currency        Order currency.
	 * @param string   $language        Data language.
	 * @param string   $description     Description.
	 * @param string   $soft_descriptor Soft descriptor.
	 */
	public function add_order_data( $order, $total, $currency, $language, $description = '', $soft_descriptor = '' ) {
		$order_data = $this->addChild( 'dados-pedido' );
		$order_data->addChild( 'numero', $order->id );
		$order_data->addChild( 'valor', number_format( $total, 2, '', '' ) );
		$order_data->addChild( 'moeda', $currency );
		$order_data->addChild( 'data-hora', str_replace( ' ', 'T', $order->order_date ) );
		if ( '' != $description ) {
			$order_data->addChild( 'descricao', $description );
		}
		$order_data->addChild( 'idioma', $language );

		if ( '' != $soft_descriptor ) {
			$order_data->addChild( 'soft-descriptor', trim( substr( $soft_descriptor, 0, 13 ) ) );
		}
	}

	/**
	 * Add the credit card data.
	 *
	 * @param string $card_number
	 * @param string $card_expiry
	 * @param string $card_cvv
	 * @param string $holder_name
	 */
	public function add_card_data( $card_number, $card_expiry, $card_cvv, $holder_name ) {
		$card_data = $this->addChild( 'dados-portador' );
		$card_data->addChild( 'numero', preg_replace( '([^0-9])', '', sanitize_text_field( $card_number ) ) );

		$expiry_date = explode( '/', sanitize_text_field( $card_expiry ) );
		$expiry_date = trim( $expiry_date[1] ) . trim( $expiry_date[0] );
		$expiry_date = ( 4 == strlen( $expiry_date ) ) ? '20' . $expiry_date : $expiry_date;
		$card_data->addChild( 'validade', $expiry_date );

		// For now all the cards must have cvv there for it is always set at 1.
		// For more information see page 11 of Manual Desenvovedor de Webservice Cielo v. 2.54.
		$card_data->addChild( 'indicador', 1 );
		$card_data->addChild( 'codigo-seguranca', preg_replace( '([^0-9])', '', sanitize_text_field( $card_cvv ) ) );
		$card_data->addChild( 'nome-portador', sanitize_text_field( $holder_name ) );
	}
	/**
	 * Add payment data.
	 *
	 * @param string $brand        Card brand.
	 * @param int    $product      Card product.
	 * @param int    $installments Installments.
	 */
	public function add_payment_data( $brand, $product, $installments ) {
		$payment_data = $this->addChild( 'forma-pagamento' );
		$payment_data->addChild( 'bandeira', $brand );
		$payment_data->addChild( 'produto', $product );
		$payment_data->addChild( 'parcelas', $installments );
	}

	/**
	 * Add return url.
	 *
	 * @param string $url wc-api URL.
	 */
	public function add_return_url( $url ) {
		$this->addChild( 'url-retorno' )->add_cdata( $url );
	}

	/**
	 * Add authorize.
	 *
	 * @param int $authorize Authorize code.
	 */
	public function add_authorize( $authorize ) {
		$this->addChild( 'autorizar', $authorize );
	}

	/**
	 * Add capture.
	 *
	 * @param string $capture Capture code.
	 */
	public function add_capture( $capture ) {
		$this->addChild( 'capturar', $capture );
	}

	/**
	 * Add token generation.
	 *
	 * @param string $token_generation Token generation (true or false).
	 */
	public function add_token_generation( $token_generation ) {
		$this->addChild( 'gerar-token', $token_generation );
	}

	/**
	 * Add TID.
	 *
	 * @param string $tid Transaction TID.
	 */
	public function add_tid( $tid ) {
		$this->addChild( 'tid', $tid );
	}

	/**
	 * Add value.
	 *
	 * @param string $value.
	 */
	public function add_value( $value ) {
		$this->addChild( 'valor', number_format( $value, 2, '', '' ) );
	}

	/**
	 * Render the formated XML.
	 *
	 * @return string
	 */
	public function render() {
		$node = dom_import_simplexml( $this );
		$dom  = $node->ownerDocument;
		$dom->formatOutput = true;

		return $dom->saveXML();
	}
}
