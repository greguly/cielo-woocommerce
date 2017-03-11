<?php
use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

/**
 * WC Cielo API Class.
 */
class WC_Cielo_API_3_0 {

	/**
	 * API version.
	 */
	const VERSION = '3.0';

	/**
	 * Gateway class.
	 *
	 * @var WC_Cielo_Gateway
	 */
	protected $gateway;

	/**
	 * Environment type.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Merchant ID and Key.
	 *
	 * @var string
	 */
	public $merchant;

	/**
	 * Charset.
	 *
	 * @var string
	 */
	protected $charset = 'ISO-8859-1';

	/**
	 * Constructor.
	 *
	 * @param WC_Cielo_Gateway $gateway
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
		$this->charset = get_bloginfo( 'charset' );
	}

    /**
     * Get the status name.
     *
     * @param  int $id Status ID.
     *
     * @return string
     */
    public function get_sale_return_status( $sale_code ) {

        $status = array(
            '00' => true,//'Transação autorizada com sucesso.',
            '000' => true,//'Transação autorizada com sucesso.',
            '01' => false,//'Transação não autorizada. Transação referida.',
            '02' => false,//'Transação não autorizada. Transação referida.',
            '03' => false,//'Transação não permitida. Erro no cadastramento do código do estabelecimento no arquivo de configuração do TEF',
            '04' => false,//'Transação não autorizada. Cartão bloqueado pelo banco emissor.',
            '05' => false,//'Transação não autorizada. Cartão inadimplente (Do not honor).',
            '06' => false,//'Transação não autorizada. Cartão cancelado.',
            '07' => false,//'Transação negada. Reter cartão condição especial',
            '08' => false,//'Transação não autorizada. Código de segurança inválido.',
            '11' => true,//'Transação autorizada com sucesso para cartão emitido no exterior',
            '12' => false,//'Transação inválida, erro no cartão.',
            '13' => false,//'Transação não permitida. Valor da transação Inválido.',
            '14' => false,//'Transação não autorizada. Cartão Inválido',
            '15' => false,//'Banco emissor indisponível ou inexistente.',
            '19' => false,//'Refaça a transação ou tente novamente mais tarde.',
            '21' => false,//'Cancelamento não efetuado. Transação não localizada.',
            '22' => false,//'Parcelamento inválido. Número de parcelas inválidas.',
            '23' => false,//'Transação não autorizada. Valor da prestação inválido.',
            '24' => false,//'Quantidade de parcelas inválido.',
            '25' => false,//'Pedido de autorização não enviou número do cartão',
            '28' => false,//'Arquivo temporariamente indisponível.',
            '39' => false,//'Transação não autorizada. Erro no banco emissor.',
            '41' => false,//'Transação não autorizada. Cartão bloqueado por perda.',
            '43' => false,//'Transação não autorizada. Cartão bloqueado por roubo.',
            '51' => false,//'Transação não autorizada. Limite excedido/sem saldo.',
            '52' => false,//'Cartão com dígito de controle inválido.',
            '53' => false,//'Transação não permitida. Cartão poupança inválido',
            '54' => false,//'Transação não autorizada. Cartão vencido',
            '55' => false,//'Transação não autorizada. Senha inválida',
            '57' => false,//'Transação não permitida para o cartão',
            '58' => false,//'Transação não permitida. Opção de pagamento inválida.',
            '59' => false,//'Transação não autorizada. Suspeita de fraude.',
            '60' => false,//'Transação não autorizada.',
            '61' => false,//'Banco emissor Visa indisponível.',
            '62' => false,//'Transação não autorizada. Cartão restrito para uso doméstico',
            '63' => false,//'Transação não autorizada. Violação de segurança',
            '64' => false,//'Transação não autorizada. Valor abaixo do mínimo exigido pelo banco emissor.',
            '65' => false,//'Transação não autorizada. Excedida a quantidade de transações para o cartão.',
            '67' => false,//'Transação não autorizada. Cartão bloqueado para compras hoje.',
            '70' => false,//'Transação não autorizada. Limite excedido/sem saldo.',
            '72' => false,//'Cancelamento não efetuado. Saldo disponível para cancelamento insuficiente.',
            '74' => false,//'Transação não autorizada. A senha está vencida.',
            '75' => false,//'Senha bloqueada. Excedeu tentativas de cartão.',
            '76' => false,//'Cancelamento não efetuado. Banco emissor não localizou a transação original',
            '77' => false,//'Cancelamento não efetuado. Não foi localizado a transação original',
            '78' => false,//'Transação não autorizada. Cartão bloqueado primeiro uso.',
            '80' => false,//'Transação não autorizada. Divergencia na data de transação/pagamento.',
            '82' => false,//'Transação não autorizada. Cartão inválido.',
            '83' => false,//'Transação não autorizada. Erro no controle de senhas',
            '85' => false,//'Transação não permitida. Falha da operação.',
            '86' => false,//'Transação não permitida. Falha da operação.',
            '89' => false,//'Erro na transação.',
            '90' => false,//'Transação não permitida. Falha da operação.',
            '91' => false,//'Transação não autorizada. Banco emissor temporariamente indisponível.',
            '92' => false,//'Transação não autorizada. Tempo de comunicação excedido.',
            '93' => false,//'Transação não autorizada. Violação de regra - Possível erro no cadastro.',
            '96' => false,//'Falha no processamento.',
            '97' => false,//'Valor não permitido para essa transação.',
            '98' => false,//'Sistema/comunicação indisponível.',
            '99' => false,//'Sistema/comunicação indisponível.',
            '999' => false,//'Sistema/comunicação indisponível.',
            'AA' => false,//'Tempo Excedido',
            'AC' => false,//'Transação não permitida. Cartão de débito sendo usado com crédito. Use a função débito.',
            'AE' => false,//'Tente Mais Tarde',
            'AF' => false,//'Transação não permitida. Falha da operação.',
            'AG' => false,//'Transação não permitida. Falha da operação.',
            'AH' => false,//'Transação não permitida. Cartão de crédito sendo usado com débito. Use a função crédito.',
            'AI' => false,//'Transação não autorizada. Autenticação não foi realizada.',
            'AJ' => false,//'Transação não permitida. Transação de crédito ou débito em uma operação que permite apenas Private Label. Tente novamente selecionando a opção Private Label.',
            'AV' => false,//'Transação não autorizada. Dados Inválidos',
            'BD' => false,//'Transação não permitida. Falha da operação.',
            'BL' => false,//'Transação não autorizada. Limite diário excedido.',
            'BM' => false,//'Transação não autorizada. Cartão Inválido',
            'BN' => false,//'Transação não autorizada. Cartão ou conta bloqueado.',
            'BO' => false,//'Transação não permitida. Falha da operação.',
            'BP' => false,//'Transação não autorizada. Conta corrente inexistente.',
            'BV' => false,//'Transação não autorizada. Cartão vencido',
            'CF' => false,//'Transação não autorizada.C79:J79 Falha na validação dos dados.',
            'CG' => false,//'Transação não autorizada. Falha na validação dos dados.',
            'DA' => false,//'Transação não autorizada. Falha na validação dos dados.',
            'DF' => false,//'Transação não permitida. Falha no cartão ou cartão inválido.',
            'DM' => false,//'Transação não autorizada. Limite excedido/sem saldo.',
            'DQ' => false,//'Transação não autorizada. Falha na validação dos dados.',
            'DS' => false,//'Transação não permitida para o cartão',
            'EB' => false,//'Transação não autorizada. Limite diário excedido.',
            'EE' => false,//'Transação não permitida. Valor da parcela inferior ao mínimo permitido.',
            'EK' => false,//'Transação não permitida para o cartão',
            'FA' => false,//'Transação não autorizada.',
            'FC' => false,//'Transação não autorizada. Ligue Emissor',
            'FD' => false,//'Transação negada. Reter cartão condição especial',
            'FE' => false,//'Transação não autorizada. Divergencia na data de transação/pagamento.',
            'FF' => true,//'Cancelamento OK',
            'FG' => false,//'Transação não autorizada. Ligue AmEx.',
            //'FG' => 'Ligue 08007285090',
            'GA' => false,//'Aguarde Contato',
            'HJ' => false,//'Transação não permitida. Código da operação inválido.',
            'IA' => false,//'Transação não permitida. Indicador da operação inválido.',
            'JB' => false,//'Transação não permitida. Valor da operação inválido.',
            'KA' => false,//'Transação não permitida. Falha na validação dos dados.',
            'KB' => false,//'Transação não permitida. Selecionado a opção incorrente.',
            'KE' => false,//'Transação não autorizada. Falha na validação dos dados.',
            'N7' => false,//'Transação não autorizada. Código de segurança inválido.',
            'R1' => false,//'Transação não autorizada. Cartão inadimplente (Do not honor).',
            'U3' => false,//'Transação não permitida. Falha na validação dos dados.',

        );

        if ( isset( $status[ $sale_code ] ) ) {
            return $status[ $sale_code ];
        }

        return null;

    }

    /**
     * Get the status name.
     *
     * @param  int $id Status ID.
     *
     * @return string
     */
    public function get_sale_return_message( $sale_code ) {

        $status = array(
            '00' => 'Transação autorizada com sucesso.',
            '000' => 'Transação autorizada com sucesso.',
            '01' => 'Transação não autorizada. Transação referida.',
            '02' => 'Transação não autorizada. Transação referida.',
            '03' => 'Transação não permitida. Erro no cadastramento do código do estabelecimento no arquivo de configuração do TEF',
            '04' => 'Transação não autorizada. Cartão bloqueado pelo banco emissor.',
            '05' => 'Transação não autorizada. Cartão inadimplente (Do not honor).',
            '06' => 'Transação não autorizada. Cartão cancelado.',
            '07' => 'Transação negada. Reter cartão condição especial',
            '08' => 'Transação não autorizada. Código de segurança inválido.',
            '11' => 'Transação autorizada com sucesso para cartão emitido no exterior',
            '12' => 'Transação inválida, erro no cartão.',
            '13' => 'Transação não permitida. Valor da transação Inválido.',
            '14' => 'Transação não autorizada. Cartão Inválido',
            '15' => 'Banco emissor indisponível ou inexistente.',
            '19' => 'Refaça a transação ou tente novamente mais tarde.',
            '21' => 'Cancelamento não efetuado. Transação não localizada.',
            '22' => 'Parcelamento inválido. Número de parcelas inválidas.',
            '23' => 'Transação não autorizada. Valor da prestação inválido.',
            '24' => 'Quantidade de parcelas inválido.',
            '25' => 'Pedido de autorização não enviou número do cartão',
            '28' => 'Arquivo temporariamente indisponível.',
            '39' => 'Transação não autorizada. Erro no banco emissor.',
            '41' => 'Transação não autorizada. Cartão bloqueado por perda.',
            '43' => 'Transação não autorizada. Cartão bloqueado por roubo.',
            '51' => 'Transação não autorizada. Limite excedido/sem saldo.',
            '52' => 'Cartão com dígito de controle inválido.',
            '53' => 'Transação não permitida. Cartão poupança inválido',
            '54' => 'Transação não autorizada. Cartão vencido',
            '55' => 'Transação não autorizada. Senha inválida',
            '57' => 'Transação não permitida para o cartão',
            '58' => 'Transação não permitida. Opção de pagamento inválida.',
            '59' => 'Transação não autorizada. Suspeita de fraude.',
            '60' => 'Transação não autorizada.',
            '61' => 'Banco emissor Visa indisponível.',
            '62' => 'Transação não autorizada. Cartão restrito para uso doméstico',
            '63' => 'Transação não autorizada. Violação de segurança',
            '64' => 'Transação não autorizada. Valor abaixo do mínimo exigido pelo banco emissor.',
            '65' => 'Transação não autorizada. Excedida a quantidade de transações para o cartão.',
            '67' => 'Transação não autorizada. Cartão bloqueado para compras hoje.',
            '70' => 'Transação não autorizada. Limite excedido/sem saldo.',
            '72' => 'Cancelamento não efetuado. Saldo disponível para cancelamento insuficiente.',
            '74' => 'Transação não autorizada. A senha está vencida.',
            '75' => 'Senha bloqueada. Excedeu tentativas de cartão.',
            '76' => 'Cancelamento não efetuado. Banco emissor não localizou a transação original',
            '77' => 'Cancelamento não efetuado. Não foi localizado a transação original',
            '78' => 'Transação não autorizada. Cartão bloqueado primeiro uso.',
            '80' => 'Transação não autorizada. Divergencia na data de transação/pagamento.',
            '82' => 'Transação não autorizada. Cartão inválido.',
            '83' => 'Transação não autorizada. Erro no controle de senhas',
            '85' => 'Transação não permitida. Falha da operação.',
            '86' => 'Transação não permitida. Falha da operação.',
            '89' => 'Erro na transação.',
            '90' => 'Transação não permitida. Falha da operação.',
            '91' => 'Transação não autorizada. Banco emissor temporariamente indisponível.',
            '92' => 'Transação não autorizada. Tempo de comunicação excedido.',
            '93' => 'Transação não autorizada. Violação de regra - Possível erro no cadastro.',
            '96' => 'Falha no processamento.',
            '97' => 'Valor não permitido para essa transação.',
            '98' => 'Sistema/comunicação indisponível.',
            '99' => 'Sistema/comunicação indisponível.',
            '999' => 'Sistema/comunicação indisponível.',
            'AA' => 'Tempo Excedido',
            'AC' => 'Transação não permitida. Cartão de débito sendo usado com crédito. Use a função débito.',
            'AE' => 'Tente Mais Tarde',
            'AF' => 'Transação não permitida. Falha da operação.',
            'AG' => 'Transação não permitida. Falha da operação.',
            'AH' => 'Transação não permitida. Cartão de crédito sendo usado com débito. Use a função crédito.',
            'AI' => 'Transação não autorizada. Autenticação não foi realizada.',
            'AJ' => 'Transação não permitida. Transação de crédito ou débito em uma operação que permite apenas Private Label. Tente novamente selecionando a opção Private Label.',
            'AV' => 'Transação não autorizada. Dados Inválidos',
            'BD' => 'Transação não permitida. Falha da operação.',
            'BL' => 'Transação não autorizada. Limite diário excedido.',
            'BM' => 'Transação não autorizada. Cartão Inválido',
            'BN' => 'Transação não autorizada. Cartão ou conta bloqueado.',
            'BO' => 'Transação não permitida. Falha da operação.',
            'BP' => 'Transação não autorizada. Conta corrente inexistente.',
            'BV' => 'Transação não autorizada. Cartão vencido',
            'CF' => 'Transação não autorizada.C79:J79 Falha na validação dos dados.',
            'CG' => 'Transação não autorizada. Falha na validação dos dados.',
            'DA' => 'Transação não autorizada. Falha na validação dos dados.',
            'DF' => 'Transação não permitida. Falha no cartão ou cartão inválido.',
            'DM' => 'Transação não autorizada. Limite excedido/sem saldo.',
            'DQ' => 'Transação não autorizada. Falha na validação dos dados.',
            'DS' => 'Transação não permitida para o cartão',
            'EB' => 'Transação não autorizada. Limite diário excedido.',
            'EE' => 'Transação não permitida. Valor da parcela inferior ao mínimo permitido.',
            'EK' => 'Transação não permitida para o cartão',
            'FA' => 'Transação não autorizada.',
            'FC' => 'Transação não autorizada. Ligue Emissor',
            'FD' => 'Transação negada. Reter cartão condição especial',
            'FE' => 'Transação não autorizada. Divergencia na data de transação/pagamento.',
            'FF' => 'Cancelamento OK',
            'FG' => 'Transação não autorizada. Ligue AmEx.',
            //'FG' => 'Ligue 08007285090',
            'GA' => 'Aguarde Contato',
            'HJ' => 'Transação não permitida. Código da operação inválido.',
            'IA' => 'Transação não permitida. Indicador da operação inválido.',
            'JB' => 'Transação não permitida. Valor da operação inválido.',
            'KA' => 'Transação não permitida. Falha na validação dos dados.',
            'KB' => 'Transação não permitida. Selecionado a opção incorrente.',
            'KE' => 'Transação não autorizada. Falha na validação dos dados.',
            'N7' => 'Transação não autorizada. Código de segurança inválido.',
            'R1' => 'Transação não autorizada. Cartão inadimplente (Do not honor).',
            'U3' => 'Transação não permitida. Falha na validação dos dados.',

        );

        if ( isset( $status[ $sale_code ] ) ) {
            return $status[ $sale_code ];
        }

        return null;

    }

    /**
     * Get the status name.
     *
     * @param  int $id Status ID.
     *
     * @return string
     */
    public function get_api_error( $error_code ) {


        $status = array(
            0   => 'Dado enviado excede o tamanho do campo',
            100 => 'Campo enviado está vazio ou invalido',
            101 => 'Campo enviado está vazio ou invalido',
            102 => 'Campo enviado está vazio ou invalido',
            103 => 'Caracteres especiais não permitidos',
            104 => 'Campo enviado está vazio ou invalido',
            105 => 'Campo enviado está vazio ou invalido',
            106 => 'Campo enviado está vazio ou invalido',
            107 => 'Campo enviado excede o tamanho ou contem caracteres especiais',
            108 => 'Valor da transação deve ser maior que \'0\'',
            109 => 'Campo enviado está vazio ou invalido',
            110 => 'Campo enviado está vazio ou invalido',
            111 => 'Campo enviado está vazio ou invalido',
            112 => 'Campo enviado está vazio ou invalido',
            113 => 'Campo enviado está vazio ou invalido',
            114 => 'O MerchantId enviado não é um GUID',
            115 => 'O MerchantID não existe ou pertence a outro ambiente (EX: Sandbox)',
            116 => 'Loja bloqueada, entre em contato com o suporte Cielo',
            117 => 'Campo enviado está vazio ou invalido',
            118 => 'Campo enviado está vazio ou invalido',
            119 => 'Nó \'Payment\' não enviado',
            120 => 'IP bloqueado por questões de segurança',
            121 => 'Nó \'Customer\' não enviado',
            122 => 'Campo enviado está vazio ou invalido',
            123 => 'Numero de parcelas deve ser superior a 1',
            124 => 'Campo enviado está vazio ou invalido',
            125 => 'Campo enviado está vazio ou invalido',
            126 => 'Prazo de validade do cartão expirou',//'Credit Card Expiration Date is invalid',
            127 => 'Numero do cartão de crédito é obrigatório',
            128 => 'Numero do cartão superiro a 16 digitos',
            129 => 'Meio de pagamento não vinculado a loja ou Provider invalido',
            130 => 'Não foi possível obter o Cartão de Crédito',
            131 => 'Campo enviado está vazio ou invalido',
            132 => 'O Merchantkey enviado não é um válido',
            133 => 'Provider enviado não existe',
            134 => 'Dado enviado excede o tamanho do campo',
            135 => 'Dado enviado excede o tamanho do campo',
            136 => 'Dado enviado excede o tamanho do campo',
            137 => 'Dado enviado excede o tamanho do campo',
            138 => 'Dado enviado excede o tamanho do campo',
            139 => 'Dado enviado excede o tamanho do campo',
            140 => 'Dado enviado excede o tamanho do campo',
            141 => 'Dado enviado excede o tamanho do campo',
            142 => 'Dado enviado excede o tamanho do campo',
            143 => 'Dado enviado excede o tamanho do campo',
            144 => 'Dado enviado excede o tamanho do campo',
            145 => 'Dado enviado excede o tamanho do campo',
            146 => 'Dado enviado excede o tamanho do campo',
            147 => 'Dado enviado excede o tamanho do campo',
            148 => 'Dado enviado excede o tamanho do campo',
            149 => 'Dado enviado excede o tamanho do campo',
            150 => 'Dado enviado excede o tamanho do campo',
            151 => 'Dado enviado excede o tamanho do campo',
            152 => 'Dado enviado excede o tamanho do campo',
            153 => 'Dado enviado excede o tamanho do campo',
            154 => 'Dado enviado excede o tamanho do campo',
            155 => 'Dado enviado excede o tamanho do campo',
            156 => 'Dado enviado excede o tamanho do campo',
            157 => 'Dado enviado excede o tamanho do campo',
            158 => 'Dado enviado excede o tamanho do campo',
            159 => 'Dado enviado excede o tamanho do campo',
            160 => 'Dado enviado excede o tamanho do campo',
            161 => 'Dado enviado excede o tamanho do campo',
            162 => 'Dado enviado excede o tamanho do campo',
            163 => 'URL de retorno não é valida - Não é aceito paginação ou extenções (EX .PHP) na URL de retorno',
            166 => 'Parâmetro AuthorizeNow é de preenchimento obrigatório',
            167 => 'Antifraude não vinculado ao cadastro do lojista',
            168 => 'Recorrencia não encontrada',
            169 => 'Recorrencia não está ativa. Execução paralizada',
            170 => 'Cartão protegido não vinculado ao cadastro do lojista',
            171 => 'Falha no processamento do pedido - Entre em contato com o suporte Cielo',
            172 => 'Falha na validação das credenciadas enviadas',
            173 => 'Meio de pagamento não vinculado ao cadastro do lojista',
            174 => 'Campo enviado está vazio ou invalido',
            175 => 'Campo enviado está vazio ou invalido',
            176 => 'Campo enviado está vazio ou invalido',
            177 => 'Campo enviado está vazio ou invalido',
            178 => 'Campo enviado está vazio ou invalido',
            179 => 'Campo enviado está vazio ou invalido',
            180 => 'Token do Cartão protegido não encontrado',
            181 => 'Token do Cartão protegido bloqueado',
            182 => 'Bandeira do cartão não enviado',
            183 => 'Data de nascimento invalida ou futura',
            184 => 'Falha no formado ta requisição. Verifique o código enviado',
            185 => 'Bandeira não suportada pela API Cielo',
            186 => 'Meio de pagamento não suporta o comando enviado',
            187 => 'ExtraData possui um ou mais nomes duplicados',
            188 => 'Avs com o CPF é invalido',
            189 => 'Dado enviado excede o tamanho do campo',
            190 => 'Dado enviado excede o tamanho do campo',
            191 => 'Dado enviado excede o tamanho do campo',
            192 => 'CEP enviado é invalido',
            193 => 'Valor para realização do SPLIT deve ser superior a 0',
            194 => 'SPLIT não habilitado para o cadastro da loja',
            195 => 'Validados de plataformas não enviado',
            196 => 'Campo obrigatório não enviado',
            197 => 'Campo obrigatório não enviado',
            198 => 'Campo obrigatório não enviado',
            199 => 'Campo obrigatório não enviado',
            200 => 'Campo obrigatório não enviado',
            201 => 'Campo obrigatório não enviado',
            202 => 'Campo obrigatório não enviado',
            203 => 'Campo obrigatório não enviado',
            204 => 'Campo obrigatório não enviado',
            205 => 'Campo obrigatório não enviado',
            206 => 'Dado enviado excede o tamanho do campo',
            207 => 'Dado enviado excede o tamanho do campo',
            208 => 'Dado enviado excede o tamanho do campo',
            209 => 'Dado enviado excede o tamanho do campo',
            210 => 'Campo obrigatório não enviado',
            211 => 'Dados da Visa Checkout invalidos',
            212 => 'Dado de Wallet enviado não é valido',
            213 => 'Cartão de crédito enviado é invalido',
            214 => 'Portador do cartão não deve conter caracteres especiais',
            215 => 'Campo obrigatório não enviado',
            216 => 'IP bloqueado por questões de segurança',
            300 => 'MerchantId was not found',
            301 => 'Request IP is not allowed',
            302 => 'Sent MerchantOrderId is duplicated',
            303 => 'Sent OrderId does not exist',
            304 => 'Customer Identity is required',
            306 => 'Merchant is blocked',
            307 => 'Transação não encontrada ou não existente no ambiente',
            308 => 'Transação não pode ser capturada - Entre em contato com o suporte Cielo',
            309 => 'Transação não pode ser Cancelada - Entre em contato com o suporte Cielo',
            310 => 'Comando enviado não suportado pelo meio de pagamento',
            311 => 'Cancelamento após 24 horas não liberado para o lojista',
            312 => 'Transação não permite cancelamento após 24 horas',
            313 => 'Transação recorrente não encontrada ou não disponivel no ambiente',
            314 => 'Invalid Integration',
            315 => 'Cannot change NextRecurrency with pending payment',
            316 => 'Não é permitido alterada dada da recorrencia para uma data passada',
            317 => 'Invalid Recurrency Day',
            318 => 'No transaction found',
            319 => 'Recorrencia não vinculada ao cadastro do lojista',
            320 => 'Can not Update Affiliation Because this Recurrency not Affiliation saved',
            321 => 'Can not set EndDate to before next recurrency',
            322 => 'Zero Dollar não vinculado ao cadastro do lojista',
            323 => 'Consulta de Bins não vinculada ao cadastro do lojista',
        );

        if ( isset( $status[ $error_code ] ) ) {
            return $status[ $error_code ];
        }

        return null;

    }

    /**
     * Get credit card brand.
     *
     * @param  string $number
     *
     * @return string
     */
    public function get_card_brand( $number ) {
        $number = preg_replace( '([^0-9])', '', $number );
        $brand  = '';

        // https://gist.github.com/arlm/ceb14a05efd076b4fae5
        $supported_brands = array(
            'Visa'       => '/^4\d{12}(\d{3})?$/',
            'Master' => '/^(5[1-5]\d{4}|677189)\d{10}$/',
            'Diners'     => '/^3(0[0-5]|[68]\d)\d{11}$/',
            'Discover'   => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
            'Elo'        => '/^((((636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})$/',
            'Amex'       => '/^3[47]\d{13}$/',
            'JCB'        => '/^(?:2131|1800|35\d{3})\d{11}$/',
            'Aura'       => '/^(5078\d{2})(\d{2})(\d{11})$/',
//            'hipercard'  => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
//            'maestro'    => '/^(?:5[0678]\d\d|6304|6390|67\d\d)\d{8,15}$/',
        );

        foreach ( $supported_brands as $key => $value ) {
            if ( preg_match( $value, $number ) ) {
                $brand = $key;
                break;
            }
        }

        return $brand;
    }

    /**
     * Process webservice payment.
     *
     * @param  WC_Order $order
     *
     * @return array
     */
    public function process_webservice_payment( $valid, $order, $response ) {

        $paymentId = json_encode( $response->getPayment()->getPaymentId() ) ;

        $status = $response->getPayment()->getStatus();
        $returnCode = $response->getPayment()->getReturnCode();
        $returnMessage = $response->getPayment()->getReturnMessage();
        $links = ($response->getPayment()->getAuthenticationUrl() != null) ? $response->getPayment()->getAuthenticationUrl() : $response->getPayment()->getUrl();

        // Set the error alert.
//        if ($status == '0' && $this->gateway->id != 'cielo_debit') {
            if (isset($returnMessage)) {
                if (!$this->gateway->get_status(trim($status, '"'))) {
                    $this->gateway->add_error((string)$this->get_api_error( $status ));
                    //$this->gateway->add_error((string)'Status - Code: ' . $status . ' Message: ' . $this->gateway->get_status_name( $status ));
//                $this->gateway->add_error( (string) $this->gateway->get_status_name( $status ) );
//                $valid = false;
                    return Array(
                        'valid' => false,
                        'payment_url' => '',
                    );
                }
                if ($this->get_api_error($status) != null) {
                    $this->gateway->add_error((string)$this->get_sale_return_message( $returnCode ));
//                    $this->gateway->add_error((string)'API - Code: ' . $status . ' Message: ' . $this->get_api_error($status));
//                $this->gateway->add_error(  (string) $this->get_api_error( $status ) );
//                $valid = false;
                    return Array(
                        'valid' => false,
                        'payment_url' => '',
                    );
                }
                if (!$this->get_sale_return_status($returnCode)) {
                    $this->gateway->add_error((string)$this->get_sale_return_message( $returnCode ));
                    $this->gateway->add_error((string)'Sale - Code: ' . $status . ' Message: ' . $this->get_api_error($status));
//                $this->gateway->add_error(  (string) $this->get_sale_return_message( $status ) );
//                $valid = false;
                    return Array(
                        'valid' => false,
                        'payment_url' => '',
                    );
                }
            }
//        }

        // Save the tid.
        if (!empty($paymentId)) {
            update_post_meta($order->id, '_transaction_id', $paymentId);
        }

        // Set the transaction URL.
        if (!empty($links)) {
            $payment_url = (string)$links;
        } else {
            $payment_url = str_replace('&amp;', '&', urldecode($this->gateway->get_api_return_url($order)));
        }

        return Array(
            'valid' => $valid,
            'payment_url' => $payment_url,
        );
        
    }

    /**
     * Process webservice payment with card brand.
     *
     * @param  Card_Brand String
     *
     * @return null
     */
    public function process_webservice_payment_card_brand( $card_brand ) {
        
        return $card_brand;
        
    }

    /**
	 * Do remote requests.
	 *
	 * @param  string $data Post data.
	 *
	 * @return array        Remote response data.
	 */
	public function do_request( $account_data ) {

		// Configure o ambiente
		if ( 'production' == $this->gateway->environment ) {
			$this->environment = $environment = Environment::production();
		} else {
            $this->environment = $environment = Environment::sandbox();
        }

        // Configure seu merchant
        $this->merchant = new Merchant( $account_data['number'], $account_data['key'] );

    }

	/**
	 * Do transaction.
	 *
	 * @param  WC_Order $order            Order data.
	 * @param  string   $id               Request ID.
	 * @param  string   $card_brand       Card brand slug.
	 * @param  int      $installments     Number of installments (use 0 for debit).
	 * @param  array    $credit_card_data Credit card data for the webservice.
	 * @param  bool     $is_debit         Check if is debit or credit.
	 *
	 * @return SimpleXmlElement|StdClass Transaction data.
	 */
	public function do_transaction(  $account_data, $payment_product, $order_total, $authorization, $order, $id, $card_brand, $installments = 0, $gateway_data = array(), $gateway = '' ) {

        $this->gateway->log->add( $this->gateway->id, 'Bandeira: '.$card_brand );

        $response_data = null;

        // Create the environment
        $this->do_request($account_data);

        $sale = new Sale($id);

        $customer = $sale->customer( trim($order->billing_first_name) . ' ' . trim($order->billing_last_name) );

        if ($installments > 0) {
            $payment = $sale->payment(number_format($order_total, 2, '', ''), $installments);
        } else {
            $payment = $sale->payment(number_format($order_total, 2, '', ''));
        }

        $payment->setAuthenticate($authorization);

//        $card_info = function ( $_card, $card_expiration, $card_number, $name_on_card ) {
//            $_card->setExpirationDate( $card_expiration );
//            $_card->setCardNumber( $card_number );
//            $_card->setHolder( $name_on_card );
//        };

        switch ($gateway) {
            case 'cielo_credit':
                $payment->creditCard($gateway_data['card_cvv'], $card_brand)
                        ->setExpirationDate( str_replace( ' ', '', $gateway_data['card_expiration'] ) )
                        ->setCardNumber( str_replace( ' ', '', $gateway_data['card_number'] ) )
                        ->setHolder( $gateway_data['name_on_card'] );
//                $card_info($card, $gateway_data['card_expiration'], $gateway_data['card_number'], $gateway_data['name_on_card']);

                break;
            case 'cielo_debit':
                // Define URL redirect back to store after bank transaction
                $payment->setReturnUrl( str_replace( '&amp;', '&', urldecode( $this->gateway->get_api_return_url( $order ) ) ) );

                $payment->debitCard($gateway_data['card_cvv'], $card_brand)
                        ->setExpirationDate( str_replace( ' ', '', $gateway_data['card_expiration'] ) )
                        ->setCardNumber( str_replace( ' ', '', $gateway_data['card_number'] ) )
                        ->setHolder( $gateway_data['name_on_card'] );
//                $card_info($card, $gateway_data['card_expiration'], $gateway_data['card_number'], $gateway_data['name_on_card']);
                break;
            case 'cielo_direct_debit':

                $payment ->setType( Payment::PAYMENTTYPE_ELECTRONIC_TRANSFER )
                         ->setProvider(str_replace(' ', '', $gateway_data['name_of_bank']));

                $payment->setReturnUrl( str_replace( '&amp;', '&', urldecode( $this->gateway->get_api_return_url( $order ) ) ) );

                break;
        }

        // Verify is Credit Gateway only
        if ($this->gateway->id == 'cielo_credit') {
            // Check if capture sale is made by Admin Order Page
            if (!$this->gateway->api->admin_sale_capture()) {
                $this->gateway->log->add($this->gateway->id, 'Automatic sale capture');
                // Automatic Capture Sale
                $payment->setCapture( true );
            }
        }

        // Verify is Credit Gateway only
        if ($this->gateway->id == 'cielo_debit') {
            // Automatic Capture Sale
            $payment->setAuthenticate( true );
        }

        try {
//            $this->gateway->log->add( $this->gateway->id, 'Erro - Gateway: ' . $gateway );

            $sale   = (new CieloEcommerce($this->merchant, $this->environment))->createSale($sale);
//            $status = $sale->getPayment()->getStatus() ;
//            $this->gateway->log->add($this->gateway->id, json_encode($sale->jsonSerialize()) );

        } catch (CieloRequestException $e) {
            // Em caso de erros de integração, podemos tratar o erro aqui.
            // os códigos de erro estão todos disponíveis no manual de integração.
            $error = $e->getCieloError();

            $sale->getPayment()->setStatus( $error->getCode() );
            $sale->getPayment()->setReturnMessage( $error->getMessage() );

            $this->gateway->log->add( $this->gateway->id, 'Erro - Code: ' . $error->getCode() );
            $this->gateway->log->add( $this->gateway->id, 'Error - Message: ' . $error->getMessage() );
            $this->gateway->log->add( $this->gateway->id, 'Error - Sale: ' . json_encode($sale->jsonSerialize()) );

        }

        $response_data = $sale;

        return $response_data;
	}

	/**
	 * Get transaction data.
	 *
	 * @param  WC_Order $order Order data.
	 * @param  string   $tid     Transaction ID.
	 * @param  string   $id      Request ID.
	 *
	 * @return SimpleXmlElement|StdClass Transaction data.
	 */
	public function get_transaction_data( $order, $tid, $id, $account_data ) {
		$sale = null;
		$response_data = null;

		$this->do_request($account_data);

		try {

			$sale = (new CieloEcommerce($this->merchant, $this->environment))->getSale( str_replace('"', '', $tid) );
			$response_data = $sale;

		} catch (CieloRequestException $e) {

			$error = $e->getCieloError();
            $this->gateway->log->add( $this->gateway->id, 'Error - Code: ' . $error->getCode() . ' Message: ' . $error->getMessage() );

		}

		return $response_data;

	}

    /**
     * Return handler.
     *
     * @param WC_Order $order Order data.
     */
    public function return_handler(Sale $response, $tid ) {

        $this->gateway->log->add($this->gateway->id, 'Cielo payment error: ' . json_encode($response->jsonSerialize()) );

        $status = $response->getPayment()->getStatus() ;
        $returnCode = $response->getPayment()->getReturnCode();
        //$returnMessage = $response->getPayment()->getReturnMessage();

        if (isset($status)) {

            // Set the error alert.
            if (!$this->gateway->get_status(trim($status, '"'))) {
                if ('yes' == $this->gateway->debug) {
                    $this->gateway->log->add($this->gateway->id, 'Cielo payment error: ' . $this->gateway->get_status_name( $status ) );
                }

                $this->gateway->add_error((string)$this->get_sale_return_message( $returnCode ));

            }

        }

        // Update the order status.
        $status = !empty($status) ? intval($status) : -1;
        $order_note = "\n";

        if ('yes' == $this->gateway->debug) {
            $this->gateway->log->add($this->gateway->id, 'Cielo payment status: ' . $status);
        }

        // For backward compatibility!
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1.12', '<=')) {
            $order_note = "\n" . 'TID: ' . $tid . '.';
        }

        if (!empty($response->getPayment()->getProofOfSale())) {
            $payment_type = $response->getPayment()->getType();

            if (method_exists($response->getPayment(), "get" . $payment_type)) {

                $payment_method = $response->getPayment()->{"get" . $payment_type}();

                $order_note .= "\n";
                $order_note .= __('Paid with', 'cielo-woocommerce');
                $order_note .= ' ';
                $order_note .= $this->gateway->get_payment_method_name(strtolower((string)$payment_method->getBrand()));
                $order_note .= ' ';

                if ('DebitCard' == $payment_type) {
                    $order_note .= __('debit', 'cielo-woocommerce');
                } elseif (('CreditCard' == $payment_type) && ((int)$response->getPayment()->getInstallments() == 1)) {
                    $order_note .= __('credit at sight', 'cielo-woocommerce');
                } else {
                    $order_note .= sprintf(__('credit %dx', 'cielo-woocommerce'), $payment_method->parcelas);
                }

            } else {

                $order_note .= "\n";
                $order_note .= __('Paid with', 'cielo-woocommerce');
                $order_note .= ' ';
                $order_note .= __('Direct Debit', 'cielo-woocommerce');
                $order_note .= ' ';
                $order_note .= $response->getPayment()->getProvider();

//                if ('DebitCard' == $payment_type) {
//                    $order_note .= __('debit', 'cielo-woocommerce');
//                } elseif (('CreditCard' == $payment_type) && ((int)$response->getPayment()->getInstallments() == 1)) {
//                    $order_note .= __('credit at sight', 'cielo-woocommerce');
//                } else {
//                    $order_note .= sprintf(__('credit %dx', 'cielo-woocommerce'), $payment_method->parcelas);
//                }

            }

            $order_note .= '.';
        }

        return Array(
            'status'     => $status,
            'order_note' => $order_note,
        );

    }

    /**
     * Do sale capture internal.
     *
     * @param  WC_Order $order Order data.
     * @param  string   $tid     Transaction ID.
     * @param  string   $id      Request ID.
     * @param  float    $amount  Amount for refund.
     *
     * @return array
     */
    public function do_sale_capture_internal( $order, $tid, $id, $amount = 0, $account_data ) {

        return (new CieloEcommerce($this->merchant, $this->environment))->captureSale(str_replace('"', '', $id), number_format( $amount, 2, '', '' ), 0, true);

    }


    /**
     * Do sale capture.
     *
     * @param  WC_Order $order Order data.
     * @param  string   $tid     Transaction ID.
     * @param  string   $id      Request ID.
     * @param  float    $amount  Amount for refund.
     *
     * @return array
     */
    public function do_sale_capture( $order, $tid, $id, $amount = 0, $account_data ) {
        $sale = null;

        $this->do_request($account_data);

        try {

            $sale = $this->do_sale_capture_internal( $order, $tid, $id, $amount, $account_data );

        //} catch (CieloRequestException $e) {
        } catch (CieloRequestException $e) {

            $cieloerror = $e->getCieloError();

        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Capturing ' . $amount . ' from order ' . $order->get_order_number() . '...' );
        }

        // Set error message.
        $error = new StdClass;
        $error->mensagem = __( 'An error occurred while trying to capturing the sale, turn on the Cielo log option and try again.', 'cielo-woocommerce' );
        if (isset($cieloerror)) {
            $error->cielocode = $cieloerror->getCode();
        }

//        if ( is_wp_error( $sale ) || ( isset( $sale['response'] ) && 200 != $sale['response']['code'] ) ) {
//        }
        if ( !isset( $sale ) ) {
            if ( 'yes' == $this->gateway->debug ) {
                $this->gateway->log->add( $this->gateway->id, 'An error occurred while capturing the transaction: Code: ' . $cieloerror->getCode() . ' - Message: ' . $cieloerror->getMessage() );
            }
            return $error;
        }

        // Error when getting the transaction response data.
        if ( !isset( $sale ) && !isset( $cieloerror ) ) {
            return $error;
        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Captured ' . $amount . ' from order ' . $order->get_order_number() . ' successfully!' );
        }

        return $sale->jsonSerialize();
    }

	/**
	 * Do transaction cancellation.
	 *
	 * @param  WC_Order $order Order data.
	 * @param  string   $tid     Transaction ID.
	 * @param  string   $id      Request ID.
	 * @param  float    $amount  Amount for refund.
	 *
	 * @return array
	 */
	public function do_transaction_cancellation( $order, $tid, $id, $amount = 0, $account_data ) {
        $this->do_request($account_data);

        try {

            $sale = (new CieloEcommerce($this->merchant, $this->environment))->cancelSale(trim($tid, '"'), number_format( $amount, 2, '', '' ));

        } catch (CieloRequestException $e) {

            $cieloerror = $e->getCieloError();

        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Refunding ' . $amount . ' from order ' . $order->get_order_number() . '...' );
        }

        // Set error message.
		$error = new StdClass;
		$error->mensagem = __( 'An error occurred while trying to cancel the payment, turn on the Cielo log option and try again.', 'cielo-woocommerce' );

        if (isset($cieloerror)) {
            $error->cielocode = $cieloerror->getCode();
        }

        if ( !isset( $sale ) ) {
            if ( 'yes' == $this->gateway->debug ) {
                $this->gateway->log->add( $this->gateway->id, 'An error occurred while canceling the transaction: Code: ' . $cieloerror->getCode() . ' - Message: ' . $cieloerror->getMessage() );
            }
            return $error;
        }

        // Error when getting the transaction response data.
        if ( !isset( $sale ) && !isset( $cieloerror ) ) {
            return $error;
        }

        if ( 'yes' == $this->gateway->debug ) {
            $this->gateway->log->add( $this->gateway->id, 'Refunded ' . $amount . ' from order ' . $order->get_order_number() . ' successfully!' );
        }

        return $sale->jsonSerialize();

	}
}
