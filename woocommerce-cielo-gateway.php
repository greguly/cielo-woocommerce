<?php
/*
Plugin Name: Cielo WooCommerce  
Plugin URI: http://omniwp.com.br/plugins/
Description: Adiciona a opção de pagamento pela Cielo ao WooCommerce - Compatível com o XML versão 1.2.0, lançado em janeiro de 2012 -
Version: 2.0.5
Author: Gabriel Reguly, omniWP, 
Author URI: http://omniwp.com.br

	Copyright: © 2012,2013 omniWP
	License: GNU General Public License v2.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

add_action('plugins_loaded', 'cielo_woocommerce_init', 0);


function cielo_woocommerce_init() {

	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	
	/**
 	 * Gateway class
 	 */
	class wc_cielo extends WC_Payment_Gateway {
	
		public function __construct() { 
			global $woocommerce;

	        $this->id			= 'cielo';
	        $this->method_title = 'Cielo';
			$this->icon 		= plugins_url( 'i/cielo.jpg' , __FILE__ );
            $this->has_fields   = true;

												
			//  meios de pagamento (bandeiras) e produtos 
			$this->descricao_meios = array( 
									'visa' => 'Visa',
									'mastercard' => 'MasterCard',
									'diners' => 'Diners',
									'discover' => 'Discover',
									'elo' => 'Elo',
									'amex' => 'American Express' );
				// credito a vista									
			$this->meios_credito = array( 
									'visa',
									'mastercard',
									'diners',
									'discover',
									'elo',
									'amex');
				// credito parcelado loja									
			$this->meios_credito_loja = array( 
									'visa',
									'mastercard',
									'diners',
									'elo',
									'amex');
				// credito parcelado cartao									
			$this->meios_credito_cartao = array( 
									'visa',
									'mastercard',
									'diners',
									'elo',
									'amex');
				// debito a vista
			$this->meios_debito = array( 'visa' );
				// valor minimo da cielo para aceitar as parcelas
			$this->valor_minimo_cielo_parcela = 5;

			// Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();
			
			// Define user set variables
			$this->title 		   = $this->settings['title'];
			$this->description 	   = $this->settings['description'];
			$this->numero          = $this->settings['numero'];
			$this->chave           = $this->settings['chave'];
			$this->mode 		   = $this->settings['mode'];
//			$this->buypage 		   = $this->settings['buypage'];
			$this->meios    	   = $this->settings['meios'];  
			$this->parcela_minima  = $this->settings['parcela_minima'];
			$this->taxa_juros      = $this->settings['taxa_juros'];
			$this->desconto_debito = $this->settings['desconto_debito'];
			$this->parcelas        = $this->settings['parcelas'];
			$this->juros 		   = $this->settings['juros'];
			$this->parcelamento    = $this->settings['parcelamento'];
			$this->captura         = $this->settings['captura'];			
			$this->autorizar       = $this->settings['autorizar'];
			

           // actions
			add_action( 'woocommerce_api_wc_cielo', array( $this, 'check_return_cielo' ) );
			
			add_action( 'return_cielo', array( $this, 'process_return_cielo') );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			
			add_action( 'woocommerce_receipt_cielo', array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_thankyou_cielo', array( $this, 'thank_you_page' ) );


			// css and js
		    wp_register_style( 'jquery-ui-css', plugins_url( '/js/theme/jquery.ui.all.css', __FILE__) );
		    wp_enqueue_style( 'jquery-ui-css' );
			wp_register_script( 'jquery-cookie', plugins_url( '/js/jquery.cookie.js',  __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'jquery-cookie' );

			$this->enabled = ( 'yes' == $this->settings['enabled'] )  && $this->testa_dados_cielo() && ! empty( $this->meios );
		} 

		/**
	     * Initialise Gateway Settings Form Fields
	     */
	    function init_form_fields() {

	    	$this->form_fields = array(
				'enabled' => array(
								'title' => 'Habilitar/Desabilitar', 
								'type' => 'checkbox', 
								'label' => 'Ativar Cielo', 
								'default' => 'no'
							), 
				'title' => array(
								'title' => 'Título', 
								'type' => 'text', 
								'description' => 'Essa opção controla o título mostrado ao cliente quando ele está no checkout.', 
								'default' => 'Cielo'
							),
				'description' => array(
								'title' => 'Descrição', 
								'type' => 'textarea', 
								'description' => 'Essa opção controla a descrição mostrada ao cliente quando ele está no checkout.', 
								'default' => 'Pague usando o método seguro da Cielo'
							),
                'mode'      => array(
                                'title' => 'Tipo',
                                'type' => 'select',
                                'options' => array( 
												'test' => 'Teste', 
												'production' => 'Produção'
								),
                                'description' => 'Selecione modo de operação teste ou produção.',
								'default' => 'test'
                            ),
/*
                'buypage'      => array(
                                'title' => 'Integração',
                                'type' => 'select',
                                'options' => array( 
												'cielo' => 'BuyPage Cielo'
								),
                                'description' => 'Selecione a modalidade de integração.',
								'default' => 'cielo'
                            ),							
*/
				'numero' => array(
								'title' => 'Número de Afiliação', 
								'description' => 'Número de afiliação da loja com a Cielo.', 
								'type' => 'text', 
								'default' => ''
							),
				'chave' => array(
								'title' =>'Chave de Afiliação', 
								'type' => 'text', 
								'description' => 'Chave de acesso da loja atribuída pela Cielo.', 
								'default' => ''
							),
 				'meios'  => array(
								'title' => 'Bandeiras Aceitas', 
                                'type' => 'multiselect',
								'description' => 'Selecione as bandeiras que serão aceitas como forma de pagamento.<br />Pressione a tecla Ctrl para selecionar mais de uma bandeira.', 
                                'options' => $this->descricao_meios,
								'default' => 'visa',
							),
 				'captura'  => array(
								'title' => 'Capturar automaticamente?', 
                                'type' => 'select',
								'description' => 'Selecione a forma de captura.', 
                                'options' => array( 
												'true' => 'Sim',
												'false' => 'Não' ),
								'default' => 'true'
							),
 				'autorizar'  => array(
								'title' => 'Autorização automática<br />( somente Visa )', 
                                'type' => 'select',
								'description' => 'Selecione a forma de autorização .', 
                                'options' => array( 
												'3' => 'Autorizar direto ( não funciona para débito )',
												'2' => 'Autorizar transação autenticada e não-autenticada ',
												'1' => 'Autorizar transação somente se autenticada',
												'0' => 'Somente autenticar a transação' ),
								'default' => '2'
							),
				'parcela_minima' => array(
								'title' => 'Parcela Mínima', 
								'type' => 'text', 
								'description' => 'Valor mínimo de cada parcela, não pode ser inferior a R$ 5,00', 
								'default' => '5.00'
							),
				'taxa_juros' => array(
								'title' => 'Taxa de Juros (%)', 
								'type' => 'text', 
								'description' => 'Taxa percentual de juros que será cobrada do cliente nas parcelas em que haja juros a ser cobrado.', 
								'default' => '2.00'
							),
				'desconto_debito' => array(
								'title' => 'Desconto Débito (%)', 
								'type' => 'text', 
								'description' => 'Desconto percentual para pagamentos feitos por débito.', 
								'default' => '0'
							),
 				'parcelas'  => array(
								'title' => 'Parcelar em até', 
                                'type' => 'select',
								'description' => 'Número máximo de parcelas para compras em sua loja.', 
                                'options' => array( 
												'1' => '1x', 
												'2' => '2x', 
												'3' => '3x', 
												'4' => '4x', 
												'5' => '5x', 
												'6' => '6x',
												'7' => '7x', 
												'8' => '8x', 
												'9' => '9x', 
												'10' => '10x', 
												'11' => '11x', 
												'12' => '12x' ),
								'default' => '1'
							),
 				'juros'  => array(
								'title' => 'Sem juros até', 
                                'type' => 'select',
								'description' => 'Número de parcelas em que não será cobrado juros do cliente.', 
                                'options' => array( 
												'1' => '1x', 
												'2' => '2x', 
												'3' => '3x', 
												'4' => '4x', 
												'5' => '5x', 
												'6' => '6x',
												'7' => '7x', 
												'8' => '8x', 
												'9' => '9x', 
												'10' => '10x', 
												'11' => '11x', 
												'12' => '12x' ),
								'default' => '6'
							),
 				'parcelamento'  => array(
								'title' => 'Tipo Parcelamento', 
                                'type' => 'select',
								'description' => 'Parcelado loja adiciona juros no total da compra', 
                                'options' => array( 
												'2' => 'Parcelado loja',
												'3' => 'Parcelado administradora' ),
								'default' => '2'
							)
				);
	    
	    } // End init_form_fields()
	    
		/**
		 * Admin Panel Options 
		 */
		public function admin_options() {
   	?>

<h3>Cielo e-Commerce</h3>
<p><em>Kit Cielo e-Commerce – Multiplataforma (disponíveis VISA, Mastercard, Elo, Diners, Discover e Amex.) <a href="http://www.cielo.com.br/portal/kit-e-commerce-cielo.html" target="_blank">http://www.cielo.com.br/portal/kit-e-commerce-cielo.html</a></em></p>
<?php
			if ( ! $this->testa_dados_cielo() ) { 
?>
<div class="inline error">
	<p><strong>Gateway Desativado</strong>: Você deve especificar a chave gerada juntamente à Cielo.</p>
</div>
<?php
			}
			if ( empty( $this->meios ) ) {
?>
<div class="inline error">
	<p><strong>Gateway Desativado</strong>: Você deve especificar as bandeiras aceitas.</p>
</div>
<?php
			
			}
			if ( get_option('woocommerce_currency') != 'BRL' ) {
?>
<div class="inline error">
	<p><strong>Erro de moeda.</strong>: A moeda selecionada não é o Real.</p>
</div>
<?php
			} else {
?>
<table class="form-table">
	<?php
	    		// Generate the HTML For the settings form.
	    		$this->generate_settings_html();
	    	?>
</table>
<?php
			}
	    } // End admin_options()
		
		/**
		 * Payment form on checkout page or on pay page
		 */
 		function payment_fields() {
			if ( empty( $this->meios ) ) {
				echo 'O plugin Cielo WooCommerce não está configurado.';				
				return;
			}

			global $woocommerce;
			$show_fields = false;
			if ( $this->description )  {
				echo wpautop( wptexturize( $this->description ) );
			}
			if ( $woocommerce->cart->needs_payment() ) {
				// Checkout: Pay for order in cart
				$valor =  $woocommerce->cart->total;
				$show_fields = 'cart';
			} else {
				if ( isset($_GET['pay_for_order']) && isset($_GET['order']) && isset($_GET['order_id']) ) {
					// Pay for existing order
					$order_key = urldecode( $_GET['order'] );
					$order_id = (int) $_GET['order_id'];
					$order = new WC_Order( $order_id );
					if ( $order->id == $order_id 
							&& $order->order_key == $order_key 
							&& in_array( $order->status, array( 'pending', 'failed' ) ) ) {
						$valor = $order->order_total;
						$show_fields = 'order';
?>
<script>
jQuery( document ).ready( function() {
	jQuery( 'body' ).trigger( 'updated_checkout' );
});
</script>
<?php						
					}
				}
			}
			if ( false != $show_fields ) {
				$valorDebito = $valor * ( 1 - ( $this->desconto_debito / 100 ) );
				// calcular nro de parcelas, respeitando a parcela mínima configurada 
				$i = $this->parcelas;
				if ( $this->parcela_minima < $this->valor_minimo_cielo_parcela )  {
					$valorMinimo = $this->valor_minimo_cielo_parcela;
				} else {
					$valorMinimo = $this->parcela_minima;
				}
				if ( ( $valor / $i ) < $valorMinimo ) {
					$i = $this->parcelas -1;
					while ( $i > 0 && ( $valor / $i ) < $this->parcela_minima ) {
						$i--;
					}
				}
?>
<div id="cielo">
	<div id="cielo-tabs">
		<ul>
			<?php
				foreach( $this->meios as $meio ) {
?>
			<li><a href="#tabs-<?php echo $meio ?>"><?php echo substr( $this->descricao_meios[$meio], 0, 6 )?></a></li>
			<?php
				}
?>
		</ul>
		<?php
				$k = 0;					
				foreach( $this->meios as $meio ) {
?>
		<div id="tabs-<?php echo $meio ?>" class="meio_de_pagamento <?php echo $meio ?>">
			<fieldset>
			<legend><?php echo $this->descricao_meios[$meio]?></legend>
			<?php 			
					if ( in_array( $meio, $this->meios_debito ) ) {					
?>
			<input type="radio" name="formaPagamento" value="<?php echo $meio . '_1_A' ?>" id="meio_<?php echo $k?>">
			<label for="meio_<?php echo $k++?>"> Débito R$ <?php echo number_format( $valorDebito, 2, ',', '.' ) ?>
			<?php 
						if ( $this->desconto_debito ) { 
							echo '<span class="desconto"> desconto de ' . $this->desconto_debito . '%</span>'; 
						}
			 ?>
			</label>
			<br />
			<?php
					} 
					if ( in_array( $meio, $this->meios_credito ) ) {
?>
			<input type="radio" name="formaPagamento" value="<?php echo $meio . '_1_1' ?>" id="meio_<?php echo $k?>">
			<label for="meio_<?php echo $k++?>"> Crédito à Vista  R$ <?php echo number_format( $valor, 2, ',', '.' ) ?></label>
			<br />
			<?php			
					}
					if ( $i > 1 ) {
						if ( '2' == $this->parcelamento ) { // parcelamento loja
							$valorJuros = number_format( $valor * $this->taxa_juros/100, 2, ',', '.' );
							$valorComJuros =  number_format( $valor * ( 1 +  $this->taxa_juros/100 ), 2, ',', '.' ) ;
							if ( in_array( $meio, $this->meios_credito_loja ) ) { 
								for ( $j = 2; $j <= $i; $j++ ) {
	?>
			<input type="radio" name="formaPagamento" value="<?php echo $meio . '_' . $j . '_2' ?>" id="meio_<?php echo $k?>">
			<label for="meio_<?php echo $k++?>"> Crédito
			<?php
									if ( $j >= $this->juros ) {
										echo $j .'x* R$ '. number_format( $valor * ( 1 + $this->taxa_juros/100 ) / $j, 2, ',', '.' ) ?>
			<span class="juros"> total R$ <?php echo $valorComJuros ?> </span>
			<?php
									} else {
										echo $j .'x R$ '. number_format( $valor/$j, 2, ',', '.' ) ?>
			<?php
									}
?>
			</label>
			<br />
			<?php				
								}
							
							}	
						} elseif ( '3' == $this->parcelamento ) { // parcelamento administradora
							if ( in_array( $meio, $this->meios_credito_loja ) ) { 
								for ( $j = 2; $j <= $i; $j++ ) {
	?>
			<input type="radio" name="formaPagamento" value="<?php echo $meio . '_' . $j . '_3' ?>" id="meio_<?php echo $k?>">
			<label for="meio_<?php echo $k++?>"> Crédito <?php echo $j ?>x R$ <?php echo number_format( $valor/$j, 2, ',', '.' ) ?> </label>
			<br />
			<?php				
								}
							}	
						}
					}	
?>
			</fieldset>
		</div>
		<?php
				}
?>
	</div>
</div>
<?php			
			}
		}
		/**
		 * Validate payment form fields
		**/
		public function validate_fields() {
			global $woocommerce;
			$valid = false;
			if ( 'cielo' == $this->get_request('payment_method') ) { 
				$valid = true;
				$formaPagamento = $this->get_request('formaPagamento');
				if ( empty( $formaPagamento ) ) {
					$woocommerce->add_error( 'Selecione o cartão e a forma de pagamento desejada' );
					$valid = false;
				}
			} 		
			return $valid;
		}
		/**
		 * Process the payment and return the result
		 **/
		function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );
			return array(
				'result' 	=> 'success',
				'redirect'	=> add_query_arg( 'formaPagamento', $this->get_request('formaPagamento'), 
							   add_query_arg( 'order', $order->id, 
							   add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id('pay') ))))
			);
		}			
		
		/**
		 * Receipt page
		 **/
		function receipt_page( $order_id ) {
			$order = new WC_Order( $order_id );

			require 'include/include.php';
			$Pedido = new Pedido();

			$Pedido->capturar  = $this->captura;
			$Pedido->autorizar = $this->autorizar;
			
			list( 
				$Pedido->formaPagamentoBandeira, 
				$Pedido->formaPagamentoParcelas,
				$Pedido->formaPagamentoProduto
				) = explode( '_',  $this->get_request( 'formaPagamento' ) );

			$descricao = $this->descricao_meios[$Pedido->formaPagamentoBandeira];
			
			switch ( $Pedido->formaPagamentoProduto ) {
				case 'A':
					$descricao .= ' Débito';
					break;
				case '1':
					$descricao .= ' Crédito';
					break;
				default:
					$descricao .= ' Crédito parcelado em ' . $Pedido->formaPagamentoParcelas . ' vezes';
			}
			
						
			if ( 'visa' != $Pedido->formaPagamentoBandeira && 3 != $Pedido->autorizar ) {
					// Obs.: Para Diners, Discover, Elo e Amex o valor será sempre “3”, 
					// pois estas bandeiras não possuem programa de autenticação.
				$Pedido->autorizar = 3;
			}
			if ( 'A' == $Pedido->formaPagamentoProduto ) {
				// débito, aplicar desconto se existente
				if ( $this->desconto_debito ) {
					$descricao .= ', com desconto de ' . $this->desconto_debito . '%';
					$order->order_total = number_format( $order->order_total * ( 1 - ( $this->desconto_debito / 100 ) ), 2 );
				}
				if ( 3 == $Pedido->autorizar ) {
					$Pedido->autorizar = 2;
				}
			} elseif ( 2 == $Pedido->formaPagamentoProduto && $Pedido->formaPagamentoParcelas >= $this->juros ) {
				// parcelamento loja, adicionar juros no total 
				$order->order_total = number_format( $order->order_total * ( 1 + $this->taxa_juros / 100 ), 2);
					$descricao .= ', com juros adicionados';
			}
			
			$Pedido->dadosPedidoNumero =  $order->id;
			$Pedido->dadosPedidoValor  = str_replace( array( ',', '.'), '', $order->order_total );
			$Pedido->urlRetorno = urlencode( htmlentities( add_query_arg('wc-api', 'wc_cielo', add_query_arg( 'retorno_cielo', 1, add_query_arg( 'order', $order->id, 	add_query_arg( 'key', $order->order_key, get_permalink(  woocommerce_get_page_id( 'thanks' ) ) ) ) ) ), ENT_QUOTES  )); 
		
			echo '<p>Forma de pagamento selecionada:<br />
				 ' . $descricao . '<br />
				 Valor do pedido R$ ' . number_format( $Pedido->dadosPedidoValor/100 , 2, ',' , '.' ) . '</p>';
			
			echo '<p>Contactando o site da Cielo...';
			// INICIA TRANSAÇÃO NO SITE DA CIELO
			if ( 'test' != $this->mode ) {
				$Pedido->dadosEcNumero = $this->numero;
				$Pedido->dadosEcChave  = $this->chave;
			} else {
				$Pedido->dadosEcNumero = CIELO;
				$Pedido->dadosEcChave  = CIELO_CHAVE;
				echo '(modo de teste).';
			}
			$objResposta = $Pedido->RequisicaoTransacao( false );

			echo '.. contato ok.</p>';
			
			$Pedido->tid             = $this->serializemmp( $objResposta->tid );
			$Pedido->pan             = $this->serializemmp( $objResposta->pan );
			$Pedido->status          = $this->serializemmp( $objResposta->status );
			$urlAutenticacao         = "url-autenticacao";
			$Pedido->urlAutenticacao = $this->serializemmp( $objResposta->$urlAutenticacao );
			
			// Grava o pedido para checar apos pagar no site da cielo
			
			$savedPedido = $Pedido;
			$transientName = 'order_' . $order->id;

			if ( is_multisite() ) {
				set_site_transient( $transientName, $savedPedido, 518400 ); // 518400 ~ 6 dias
			} else {
				set_transient( $transientName, $savedPedido, 518400 );
			}
			echo '
				<a class="button pay"    href="' . esc_url( $objResposta->$urlAutenticacao ) . '">'. 'Pagar' . '</a>
				<a class="button order"  href="' . get_permalink( woocommerce_get_page_id( 'checkout' ) ) . '">'. 'Checkout' . '</a>
				<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ).'">'  . 'Cancelar o pedido.' . '</a>';
		}
		
		function process_return_cielo( $order_id ) {
			if ( $order_id ) {
				$order = new WC_Order( $order_id );
				if ( $order->status != 'processing' && $order->status != 'completed' ) { 
					require 'include/include.php';
					$Pedido = new Pedido();
					$transientName = 'order_' . $order->id;
					// recupera o pedido gravado 
					if ( is_multisite() ) {
						$Pedido = get_site_transient( $transientName ); 
					} else {
						$Pedido = get_transient( $transientName ); 
					}
					if ( false === $Pedido || empty( $Pedido ) ) {
						$order->update_status( 'failed',  'Your payment session has expired. Please start over!' );
					} else {
						$Pedido->tid             = $this->unserializemmp( $Pedido->tid );
						$Pedido->pan             = $this->unserializemmp( $Pedido->pan );
						$Pedido->status          = $this->unserializemmp( $Pedido->status );
						$Pedido->urlAutenticacao = $this->unserializemmp( $Pedido->urlAutenticacao );
		
						// Consulta situação da transação
						$objResposta = $Pedido->RequisicaoConsulta();
						
						// Atualiza status
						$Pedido->status = $objResposta->status;
						if ( $Pedido->status != '4' && $Pedido->status != '6' ) {
							// Order failed
							$order->update_status( 'failed', $Pedido->getStatus() );
						} else {
							// Order paid
							$order->add_order_note( $Pedido->getStatus() . ' TID:' . $Pedido->tid );
							// Reduce stock levels
							$order->reduce_order_stock();
							$order->payment_complete();
						}
						if ( is_multisite() ) {
							delete_site_transient( $transientName );
						} else {
							delete_transient( $transientName );
						}
					}
				}
				header( 'location:' . add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink(  woocommerce_get_page_id( 'thanks' ) )  ) ) ); 
				exit;				
			}
		}
		
		function check_return_cielo() {
			if ( $this->get_request( 'retorno_cielo' ) ) {
				$order_id  = $this->get_request( 'order' );
				$order_key = $this->get_request( 'key' );
				if ( $order_id ) {
					$order = new WC_Order( $order_id );
					if ( $order->order_key == $order_key ) {
						do_action( 'return_cielo', $order->id );
					}
				}
			}
		}
		
        /**
        * Thank you page
        */
		function thank_you_page () {
			global $woocommerce;
			$order_id = $this->get_request( 'order' );
			$order = new WC_Order( $order_id );
			//check again the status of the order
			if ( $order->status == 'processing' || $order->status == 'completed' ) {                                                     
                //display additional success message
				echo '<p>Seu pagamento para ' . woocommerce_price( $order->order_total ) . ' foi recebido com sucesso. O código de autorização foi gerado, <a href="' .  add_query_arg('order', $order_id, get_permalink( woocommerce_get_page_id( 'woocommerce_view_order' ) ) ) . '">Clique aqui para ver seu pedido</a></p>';
				do_action( 'woocommerce_thankyou_' . $order->payment_method, $order->id );
				do_action( 'woocommerce_thankyou', $order->id );
			} else {
				//display additional failed message
				echo '<p>Para maiores informações ou dúvidas quanto ao seu pedido, <a href="'. add_query_arg('order', $order_id, get_permalink( woocommerce_get_page_id( 'woocommerce_view_order' ) ) ) . '">Clique aqui para ver seu pedido</a> .</p>';
			}
		}		
		/**
		 * Testa se os dados da Cielo estão preenchidos
		 */
		function testa_dados_cielo() {
			if ( 'test' != $this->mode  && ( empty( $this->numero ) || empty( $this->chave ) ) ) {
				return false;
			} else {
				return true;
			}
		}
		/**
		 * Funções auxiliares para os transients
		 */
		function serializemmp( $to_serialize ) {
			if( $to_serialize instanceof SimpleXMLElement ) {
				$stdClass = new stdClass();
				$stdClass->type = get_class( $to_serialize );
				$stdClass->data = $to_serialize->asXml();
				return serialize( $stdClass );
			}
			return $to_serialize;
		}
		function unserializemmp( $to_unserialize ) {
			$to_unserialize = unserialize( $to_unserialize );
			if ( $to_unserialize instanceof  stdClass ) {
				if ( $to_unserialize->type == "SimpleXMLElement" ){
					$to_unserialize = simplexml_load_string( $to_unserialize->data );
				}
			}
			return $to_unserialize;
		}
	
		/**
		 * Get $_REQUEST data if set
		 **/
		private function get_request( $name ) {
			if ( isset( $_REQUEST[ $name ] ) ) {
				return $_REQUEST[ $name ];
			} else {
				return NULL;
			}
		}
	}

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function cielo_woocommerce_add_cielo_gateway( $methods ) {
		$methods[] = 'wc_cielo';
		return $methods;
	}
	
	
	
	/**
	 * Add a direct link to settings 
	 */
	function cielo_woocommerce_plugin_action_links( $links, $file ) {
		if ( $file == 'cielo-woocommerce/woocommerce-cielo-gateway.php' ) {
			$settings_link = '<a href="' . admin_url( '?page=woocommerce&tab=payment_gateways&section=wc_cielo') . '">Configuração</a>';
//			$settings_link .= '<a href="http://omniwp.com.br/">Suporte Comercial</a>';
			array_unshift( $links, $settings_link ); // before other links
		}
		return $links;
	}
	
	/**
	 * Add a notice if configuration is due
	 */
	function cielo_woocommerce_plugin_notice( $plugin ) {
		if ( $plugin == 'cielo-woocommerce/woocommerce-cielo-gateway.php' ) {
			echo '<td colspan="5" class="plugin-update"><a href="' . admin_url( '?page=woocommerce&tab=payment_gateways&section=wc_cielo') . '">Você precisa configurar o Cielo WooCommerce.</a></td>';
		}
	}


	add_filter( 'woocommerce_payment_gateways', 'cielo_woocommerce_add_cielo_gateway' );
	add_filter( 'plugin_action_links',          'cielo_woocommerce_plugin_action_links', 10, 2 );
	add_action( 'after_plugin_row', 'cielo_woocommerce_plugin_notice' );

} 
?>