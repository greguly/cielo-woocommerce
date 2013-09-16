<?php
	$logFile = LOGS_DIR . 'log.txt';

	// Verifica em Resposta XML a ocorrência de erros 
	// Parâmetros: XML de envio, XML de Resposta
	function VerificaErro( $vmPost, $vmResposta ) {
		global $woocommerce;
		$error_msg = null;
		try {
			if(stripos($vmResposta, "SSL certificate problem") !== false)
			{
				throw new Exception("CERTIFICADO INVÁLIDO - O certificado da transa&ccedil;&atilde;o não foi aprovado", "099");
			}
			
			$objResposta = simplexml_load_string($vmResposta, null, LIBXML_NOERROR);
			if($objResposta == null)
			{
				throw new Exception("HTTP READ TIMEOUT - o Limite de Tempo da transa&ccedil;&atilde;o foi estourado", "099");
			}
		} catch (Exception $ex) {
			$error_msg = "     C&oacute;digo do erro: " . $ex->getCode() . "\n";
			$error_msg .= "     Mensagem: " . $ex->getMessage() . "\n";
/**/	
			// Gera página HTML
			echo '<html><head><title>Erro sint&aacute;tico na transa&ccedil;&atilde;o</title></head><body>';
			echo '<span style="color:red;, font-weight:bold;">Ocorreu um erro na comunica&ccedil;&atilde;o com a Cielo!</span>' . '<br />';
			echo '<span style="font-weight:bold;">Detalhes do erro:</span>' . '<br />';
			echo '<pre>' . $error_msg . '<br />';
//			echo "<br />     XML de envio: " . "<br />" . htmlentities($vmPost);
//			echo "<br />     XML de retorno: " . "<br />" . htmlentities($vmResposta);
			echo '</pre></body></html>';
			$error_msg .= "     XML de envio: " . "\n" . $vmPost;
			$error_msg .= "     XML de retorno: " . "\n" . $vmResposta;
		
			// Dispara o erro
			trigger_error( $error_msg, E_USER_ERROR );
/**/
			$woocommerce->add_error( $error_msg );
			 /* Don't execute PHP internal error handler */
		    return true;
		}
		
		if ( $objResposta->getName() == "erro" ) {
			$error_msg = "     C&oacute;digo do erro: " . $objResposta->codigo . "\n";
			$error_msg .= "     Mensagem: " . utf8_decode($objResposta->mensagem) . "\n";


			// Gera página HTML
			echo '<html><head><title>Erro na transa&ccedil;&atilde;o - </title></head><body>';
			echo '<span style="color:red;, font-weight:bold;">Ocorreu um erro em sua transa&ccedil;&atilde;o!</span>' . '<br />';
			echo '<span style="font-weight:bold;">Detalhes do erro:</span>' . '<br />';
			echo '<pre>' . utf8_encode( $error_msg ) . '<br />';
			echo "<br />     XML de envio: " . "<br />" . htmlentities($vmPost);
			echo "<br />     XML de retorno: " . "<br />" . htmlentities($vmResposta);
			echo '</pre></body></html>';

			$error_msg .= "     XML de envio: " . "\n" . $vmPost;
			$error_msg .= "     XML de retorno: " . "\n" . $vmResposta;
		
			// Dispara o erro
			trigger_error( $error_msg, E_USER_ERROR );

			$woocommerce->add_error( $error_msg );
			 /* Don't execute PHP internal error handler */
		    return true;
		}
	}
	
	
	// Grava erros no arquivo de log
	function Handler($eNum, $eMsg, $file, $line, $eVars) {

		if ( in_array( $eNum, array( E_NOTICE, E_STRICT ) ) ) {
			return;
		}
		$logFile = LOGS_DIR . 'log.txt';
		$e = "";
		$Data = date("Y-m-d H:i:s (T)");
		
		$errortype = array(
				E_ERROR 			=> 'ERROR',
				E_WARNING			=> 'WARNING',
				E_PARSE				=> 'PARSING ERROR',
				E_NOTICE			=> 'RUNTIME NOTICE',
				E_CORE_ERROR		=> 'CORE ERROR',
				E_CORE_WARNING      => 'CORE WARNING',
                E_COMPILE_ERROR     => 'COMPILE ERROR',
                E_COMPILE_WARNING   => 'COMPILE WARNING',
                E_USER_ERROR        => 'ERRO NA TRANSACAO',
                E_USER_WARNING      => 'USER WARNING',
                E_USER_NOTICE       => 'USER NOTICE',
                E_STRICT            => 'RUNTIME NOTICE',
                E_RECOVERABLE_ERROR	=> 'CATCHABLE FATAL ERROR'
				);

		$e .= "**********************************************************\n";
		$e .= $eNum . " " . $errortype[$eNum] . " - ";
		$e .= $Data . "\n";
		//$e .= "     DEBUG_BACKTRACE: <b>" . __FUNCTION__ . '</b><pre>' . print_r( debug_backtrace(), true ) . '</pre><hr />' ."\n";
		$e .= "     ARQUIVO: " . $file . "(Linha " . $line .")\n";
		$e .= "     MENSAGEM: " . "\n" . $eMsg . "\n\n";
		
		if ( E_NOTICE != $eNum	) {	
			if ( ! error_log($e, 3, $logFile) ) {
				echo $e;
			}
			exit();
		}
	}
	
	$olderror = set_error_handler("Handler");
	ini_set('error_log', $logFile);
	ini_set('log_errors', 'On');
	ini_set('display_errors', 'On');
	ini_set("date.timezone", "America/Sao_Paulo");
	ini_set('error_reporting', E_ALL ^ NOTICE);
?>