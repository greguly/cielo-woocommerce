<?php
define( 'LOGS_DIR', substr( plugin_dir_path(__FILE__), 0,  -8 ) . 'logs/' );
define( 'CA_INFO',  plugin_dir_path(__FILE__) . 'ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt' );

require 'errorHandling.php';
require_once 'pedido.php';
require_once 'logger.php';

define('VERSAO', "1.2.0");


// CONSTANTES
if ( 'test' == $this->mode ) {
	define("ENDERECO_BASE", "https://qasecommerce.cielo.com.br");
} else {	
	define("ENDERECO_BASE", "https://ecommerce.cielo.com.br");
}
define("ENDERECO", ENDERECO_BASE."/servicos/ecommwsec.do");


define("LOJA", "1006993069");
define("LOJA_CHAVE", "25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3");
define("CIELO", "1001734898");
define("CIELO_CHAVE", "e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832");


// Envia requisiчуo
function httprequest($paEndereco, $paPost){

	$sessao_curl = curl_init();
	curl_setopt($sessao_curl, CURLOPT_URL, $paEndereco);
	
	curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

	//  CURLOPT_SSL_VERIFYPEER
	//  verifica a validade do certificado
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, true);
	//  CURLOPPT_SSL_VERIFYHOST
	//  verifica se a identidade do servidor bate com aquela informada no certificado
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 2);

	//  CURLOPT_SSL_CAINFO
	//  informa a localizaчуo do certificado para verificaчуo com o peer
	curl_setopt($sessao_curl, CURLOPT_CAINFO, CA_INFO );
	curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 3);

	//  CURLOPT_CONNECTTIMEOUT
	//  o tempo em segundos de espera para obter uma conexуo
	curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 10);

	//  CURLOPT_TIMEOUT
	//  o tempo mсximo em segundos de espera para a execuчуo da requisiчуo (curl_exec)
	curl_setopt($sessao_curl, CURLOPT_TIMEOUT, 40);

	//  CURLOPT_RETURNTRANSFER
	//  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
	//  invщs de imprimir o resultado na tela. Retorna FALSE se hс problemas na requisiчуo
	curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($sessao_curl, CURLOPT_POST, true);
	curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $paPost );

	$resultado = curl_exec($sessao_curl);
	
	curl_close($sessao_curl);

	if ($resultado)
	{
		return $resultado;
	}
	else
	{
		return curl_error($sessao_curl);
	}
}
?>