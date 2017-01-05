=== Cielo WooCommerce - Solução Webservice ===
Contributors: Gabriel Reguly, claudiosanches, gopaulo
Donate link: https://claudiosmweb.com/doacoes/
Tags: woocommerce, cielo, payment gateway
Requires at least: 3.9
Tested up to: 4.7
Stable tag: 4.0.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Brazilian payment gateway Cielo to WooCommerce

== Description ==

Utilize a Solução Webservice da Cielo em sua loja WooCommerce e permita os seus clientes pagarem com cartão de crédito ou débido.

A [Cielo](http://www.cielo.com.br/) é uma empresa líder em soluções de pagamentos eletrônicos na América Latina. Responsável pelo credenciamento de estabelecimentos comerciais, e pela captura, transmissão, processamento e liquidação financeira das transações realizadas com cartões de crédito e débito, capturando transações das maiores bandeiras de cartões do mundo.

= Estão disponíveis as seguintes bandeiras: =

* Visa (crédito e débido)
* MasterCard (crédito e débido)
* Diners Club
* Discover
* Elo
* American Express
* JCB
* Aura

= Com este plugin é possível trabalhar com: =

* Solução Webservice.
* BuyPage Cielo (antigo e será desativado pela Cielo em breve).

Nota: O Checkout Cielo esta disponível no plugin [WooCommerce Checkout Cielo](https://wordpress.org/plugins/woocommerce-checkout-cielo/).

= Desenvolvimento =

O plugin **Cielo WooCommerce** foi desenvolvido sem nenhum incentivo da Cielo. Isto quer dizer que nenhum dos desenvolvedores deste plugin possuem vínculos com a Cielo e contamos com a sua ajuda para melhorar o código e o funcionamento deste plugin.

Toda a integração foi desenvolvida a partir da última versão do [Manual do Desenvolvedor da Cielo](https://www.cielo.com.br/ecommerce).

= Compatibilidade =

Compatível desde a versão 2.3.x até 2.6.x do [WooCommerce](http://wordpress.org/plugins/woocommerce/).

= Instalação =

Confira o nosso guia de instalação e configuração da Cielo na aba [Installation](http://wordpress.org/plugins/cielo-woocommerce/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/plugins/cielo-woocommerce/faq/).
* O fórum de suporte do [WordPress](http://wordpress.org/support/plugin/cielo-woocommerce) (apenas em inglês).
* O fórum de suporte do [WordPress Brasil](http://br.forums.wordpress.org/forum/plugins-e-codigos) utilizando as tags "cielo" e "woocommerce".
* O nosso fórum de suporte no [GitHub](https://github.com/greguly/cielo-woocommerce/issues).

= Colaborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/greguly/cielo-woocommerce).

= Agradecimentos =

* [Anilton Veiga](https://www.behance.net/aniltonveiga) - Criação do design/ícones do formulário de pagamento.

== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.
* Para mais detalhes sobre a instalação de plugins no WordPress leia o tutorial [WordPress - Gerenciando Plugins](http://codex.wordpress.org/pt-br:Gerenciando_Plugins#Instalando_Plugins).

= Requerimentos: =

* Ter um site pronto com o WordPress e WooCommerce instalados.
* Utilizar um certificado SSL (é recomendado um de 2048 bits).
* Ler o material "[Boas Práticas de Segurança no e-Commerce](http://www.cielo.com.br/documents/b771c7655be54bc983aba229162f0faa.pdf)" da Cielo.
* Ter cadastro no [Cielo e-Commerce](http://www.cielo.com.br/portal/cielo/solucoes-de-tecnologia/e-commerce.html), faça o seu cadastro utilizando:
    * [Cadastro de clientes do Banco do Brasil, Bradesco ou HSBC](http://www.cielo.com.br/sitecielo/afiliacao/credenciamentoafiliacaonaologado.html)
    * [Cadastro de clientes dos demais bancos](http://www.cielo.com.br/sitecielo/e-commerce/credenciamento-ecommerce.html)

= Configurações do plugin: =

É possível acessar a tela de configurações do plugin na página de administração do WordPress em `WooCommerce -> Configurações -> Finalizar Compra -> Cielo`.

O plugin funciona com os ambientes de **Teste** e **Produção**, onde você deve utilizar o ambiente de **Teste** para testar a integração antes de fazer a homologação. Uma vez que estiver homologado, você poderá ter utilizar o ambiente de **Produção** onde é possível entrar com o **número** e a **chave** de afiliação do estabelecimento com a Cielo.

#### Notas sobre o ambiente de testes ####

No ambiente de **Testes** é possível utilizar alguns cartões de teste disponíveis no [Manual do Desenvolvedor Cielo](https://www.cielo.com.br/ecommerce), basta fazer o download do kit e ler o arquivo "Manual_Desenvolvedor_WebService_XXX_vX.pdf".

Outra coisa importante é saber que o ambiente de **Testes** tem alguns comportamentos peculiares que você deve conhecer para poder utilizá-lo com eficiência:

* Para simular pagamentos aprovados o valor total do pedido não pode ter nenhum centavo, exemplo: `R$ 100,00`.
* É possível simular pagamentos recusados fazendo pedidos com o valor total contendo centavos, exemplo: `R$ 100,99`.
* Os cartões do kit de integração que estão marcados com a opção "Autenticação" poderão redirecionar para uma página em branco com um XML e um botão, logo após inserir os dados dentro da página da Cielo, isto é normal e serve para "simular" a autenticação dentro do banco do cliente, basta clicar no botão e você será redirecionado novamente para a sua loja (não se preocupe, o cliente não irá ver esta tela no ambiente de produção e sim o site de seu banco).
* Ao tentar simular uma compra, observe que o juros as vezes pode adicionar centavos no total e isso vai simular uma transação recusada (parece besta, mas muita gente quer simular pagamentos aprovados e acaba forçando todos para serem recusados por causa disso).

== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* WooCommerce versão 2.1.x ou superior instalado e ativado.
* Seguir o nosso [guia de instalação](http://wordpress.org/plugins/cielo-woocommerce/installation/).

= São aceitas quais moedas? =

No momento apenas Real (BRL).

= Quais são as bandeiras disponíveis? =

* Visa (crédito e débido)
* MasterCard (crédito e débido)
* Diners Club
* Discover
* Elo
* American Express
* JCB
* Aura

= Quais são os modelos de checkout disponíveis? =

* Solução Webservice.
* BuyPage Cielo (antigo e será desativado pela Cielo em breve).

= Como funciona a Solução Webservice? =

Com a Solução Webservice a digitação dos dados do cartão será no ambiente da loja. Ou seja, a própria loja se encarrega do desenvolvimento de uma página segura na Internet (HTTPS), respeitando políticas de segurança, para capturar os dados do cartão do portador.

= Como funciona a BuyPage Cielo? =

Na BuyPage Cielo a digitação dos dados do cartão será no ambiente da Cielo. Geralmente é aconselhada a clientes que não queiram lidar com questões de segurança e desejam utilizar a infraestrutura da Cielo.

Entretanto a Cielo esta desativando esta opção em favor do Checkout Cielo, então em breve o BuyPage Cielo não irá mais funcionar.

= É possível usar o pagamento por celular? =

No momento ainda não é possível, entretanto pretendemos integrar no futuro.

= É possível fazer pagamentos recorrentes/assinaturas? =

Não é possível no momento, mas será entregado no futuro.

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= É possível cancelar o pagamento/devolver o dinheiro do cliente pelo plugin? =

Sim é possível fazer reembolsos do valor total ou parcial, entretanto apenas para transações feitas em menos de 120 dias, além de que é necessário utilizar WooCommerce 2.2 ou superior.

Os reembolsos devem ser feito dentro da página do pedido na tela de administração do WordPress em "WooCommerce > Pedidos", sendo possível encontrar a opção para isso clicando no botão "Reembolso" na tabela de itens do pedido.

**Nota:** Para pagamentos com débito e para cartão de crédito da bandeira Discover é possível apenas realizar o reembolso do valor total do pedido.

= Aconteceu um erro, o que eu faço? =

Sempre que ocorrer um erro você deve ativar a opção de log do plugin e tentar simular o erro novamente, desta forma o erro será gravado no arquivo de log e você poderá saber o que aconteceu.

Não é um problema caso você não consiga entender o arquivo de log, pois você pode salvar o conteúdo dele utilizando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com) solicitar ajuda usando:

* O fórum de suporte do [WordPress](http://wordpress.org/support/plugin/cielo-woocommerce) (apenas em inglês).
* O fórum de suporte do [WordPress Brasil](http://br.forums.wordpress.org/forum/plugins-e-codigos) utilizando as tags "cielo" e "woocommerce".
* O nosso fórum de suporte no [GitHub](https://github.com/greguly/cielo-woocommerce/issues).

= O que fazer quando tentar finalizar a compra aparece a mensagem "Cielo: Um erro aconteceu ao processar o seu pagamento, por favor, tente novamente ou entre em contato para conseguir assistência"? =

Esta mensagem geralmente irá aparecer quando o seu servidor tiver problemas para fazer a conexão com a Cielo. Mas é possível saber com certeza o que aconteceu de errado utilizando a opção de log do plugin como descrito na sessão acima.

== Screenshots ==

1. Configurações para cartão de crédito.
1. Configurações para cartão de débito.
2. Página de finalização utilizando o tema Storefront, mostrando as opções de crédito e débito na Solução Webservice.

== Changelog ==

= 4.0.14 - 2017/01/05 =

* Força sempre limpar os dados do cartão quando algo acontece qualquer erro na finalização do pedido.

= 4.0.13 - 2016/12/24 =

* Melhorada a exibição das opções de juros para cartão de crédito.

= 4.0.12 - 2016/11/07 =

* Corridigo erro quando taxa de juros é igual ou menor do que zero.

= 4.0.11 - 2016/06/30 =

* Suporte para WooCommerce 2.6.
* Melhorada a URL de retorno que em alguns servidores não funcionava.
* Corrigido método de débito sendo recusado mesmo quando o pagamento esta em andamento.

= 4.0.10 - 2015/08/09 =

* Corrigido limite de caracteres para o campo de cartão (não estava funcionando para cartão Aura).
* Suporte confirmado para WooCommerce 2.4.

= 4.0.9 - 2015/05/14 =

* Correção do método de reembolsos.
* Removido código antigo que fazia reembolsos quando alterado o pédido para cancelado (veja a documentação do plugin para mais detalhes).
* Correção das opções de autenticação.
* Correção da autenticação para cartão de débito.

= 4.0.8 - 2015/05/08 =

* Corrigido os dados enviados para a Solução Webservice de débito e crédito.

= 4.0.7 - 2015/05/08 =

* Alterado os atributos "name" dos campos de débito e crédito para a Solução Webservice.
* Corrigido erros na identificação da bandeira de cartões com a Solução Webservice.

= 4.0.6 - 2015/05/03 =

* Correção da validação de bandeira dos cartões de débito para a Solução Webservice.

= 4.0.5 - 2015/05/01 =

* Correção da tradução.

= 4.0.4 - 2015/04/30 =

* Corrigida validação de parcelas.
* Adicionado método em PHP para detectar a bandeira do cartão de crédito e removido o método antigo em JavaScript.

= 4.0.3 - 2015/04/26 =

* Corrigida verificação de SSL no checkout, este erro estava impedindo de exibir as opções em produção para a Solução Webservice.
* Alterado o limite para cancelamento/reembolso de 90 dias para 120 dias, como manda a nova documentação da Cielo.

= 4.0.2 - 2015/04/13 =

* Corrigida a finalização com cartão de crédito quando as parcelas é configurada como 1.

= 4.0.1 - 2015/04/11 =

* Corrigida a validação das parcelas quando configurado o número de parcelas como 1.

= 4.0.0 - 2015/04/10 =

* Implementada a Solução Webservice (funciona apenas para WooCommerce 2.3 ou superior).
* Adicionado esquema de templates para tornar possível a manipulação dos formulários de checkout.
* Depreciado o filtro `wc_cielo_form_path`.
* Refatorado todo o código do plugin dividindo em dois gateways, uma para crédito e outro para débito.
* Corrigido do parcelamento, agora funciona corretamente com o juros por mês.
* Alterado nome do plugin para "Cielo WooCommerce - Solução Webservice".

== Upgrade Notice ==

= 4.0.14 =

* Força sempre limpar os dados do cartão quando algo acontece qualquer erro na finalização do pedido.
