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

		$node->appendChild( $no->createCDATASection( $string ) );
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
	 * @param WC_Order $order       WooCommerce order data.
	 * @param float    $total       Order total.
	 * @param int      $currency    Order currency.
	 * @param string   $language    Data language.
	 * @param string   $description Description.
	 */
	public function add_order_data( $order, $total, $currency, $language, $description = '' ) {
		$order_data = $this->addChild( 'dados-pedido' );
		$order_data->addChild( 'numero', $order->id );
		$order_data->addChild( 'valor', number_format( $total, 2, '', '' ) );
		$order_data->addChild( 'moeda', $currency );
		$order_data->addChild( 'data-hora', str_replace( ' ', 'T', $order->order_date ) );
		if ( '' != $description ) {
			$order_data->addChild( 'descricao', $description );
		}
		$order_data->addChild( 'idioma', $language );
		$order_data->addChild( 'soft-descriptor', '' );
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
		$this->addChild( 'url-retorno', $url );
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
