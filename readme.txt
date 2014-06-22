=== Cielo WooCommerce ===
Contributors: Gabriel Reguly, claudiosanches
Donate link: http://omniwp.com.br/donate/
Tags: WooCommerce, Payment Gateway, Cielo
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Brazilian payment gateway Cielo to WooCommerce

== Description ==

### Adds Brazilian payment gateway Cielo to WooCommerce. ###

The Cielo WooCommerce plugin enables integration with WooCommerce and the Cielo Payment Gateway (for Brazilian Merchants).

Please notice that WooCommerce must be installed and active.

= Contribute =

You can contribute to the source code in our [GitHub](https://github.com/greguly/cielo-woocommerce) page.

### Descrição em Português: ###

Utilize a Cielo em sua loja WooCommerce e permita os seus clientes pagarem com cartão de crédito ou débido.

A [Cielo](http://www.cielo.com.br/) é uma empresa líder em soluções de pagamentos eletrônicos na América Latina. Responsável pelo credenciamento de estabelecimentos comerciais, e pela captura, transmissão, processamento e liquidação financeira das transações realizadas com cartões de crédito e débito, capturando transações das maiores bandeiras de cartões do mundo.

Estão disponíveis as seguintes bandeiras:

* Visa (crédito e débido)
* MasterCard (crédito e débido)
* Diners Club
* Discover
* Elo
* American Express
* JCB
* Aura

Com este plugin é possível escolher entre os modelos de *BuyPage*:

* Cielo
* Loja (ainda não implementado, será possível em breve)

= Desenvolvimento =

O plugin **Cielo WooCommerce** foi desenvolvido sem nenhum incentivo da Cielo. Isto quer dizer que nenhum dos desenvolvedores deste plugin possuem vínculos com a Cielo e contamos com a sua ajuda para melhorar o código e o funcionamento deste plugin.

Toda a integração foi desenvolvida a partir da última versão do [kit de integração da Cielo](http://www.cielo.com.br/portal/cielo/solucoes-de-tecnologia/e-commerce.html).

= Compatibilidade =

Compatível com as versões 2.0.x e 2.1.x do [WooCommerce](http://wordpress.org/plugins/woocommerce/).

= Instalação =

Confira o nosso guia de instalação e configuração da Cielo na aba [Installation](http://wordpress.org/plugins/cielo-woocommerce/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/plugins/cielo-woocommerce/faq/).
* O fórum de suporte do [WordPress](http://wordpress.org/support/plugin/cielo-woocommerce) (apenas em inglês).
* O fórum de suporte do [WordPress Brasil](http://br.forums.wordpress.org/forum/plugins-e-codigos) utilizando as tags "cielo" e "woocommerce".
* O nosso fórum de suporte no [GitHub](https://github.com/greguly/cielo-woocommerce/issues).

= Coloborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/greguly/cielo-woocommerce).

== Installation ==

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Navigate to WooCommerce -> Settings -> Payment Gateways, choose Cielo and fill the plugin options.

### Instalação e configuração em Português: ###

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

No ambiente de **Testes** é possível utilizar alguns cartões de teste disponíveis no [kit de integração da Cielo](http://www.cielo.com.br/portal/cielo/solucoes-de-tecnologia/e-commerce.html), basta fazer o download do kit e ler o arquivo "Cielo e-Commerce - Manual do Desenvolvedor X.X.X.X.pdf" que esta dentro da pasta "Materiais Técnicos".

Outra coisa importante é saber que o ambiente de **Testes** tem alguns comportamentos peculiares que você deve conhecer para poder utilizá-lo com eficiência:

* Para simular pagamentos aprovados o valor total do pedido não pode ter nenhum centavo, exemplo: `R$ 100,00`.
* É possível simular pagamentos recusados fazendo pedidos com o valor total contendo centavos, exemplo: `R$ 100,99`.
* Os cartões do kit de integração que estão marcados com a opção "Autenticação" poderão redirecionar para uma página em branco com um XML e um botão, logo após inserir os dados dentro da página da Cielo, isto é normal e serve para "simular" a autenticação dentro do banco do cliente, basta clicar no botão e você será redirecionado novamente para a sua loja (não se preocupe, o cliente não irá ver esta tela no ambiente de produção e sim o site de seu banco).
* Ao tentar simular uma compra, observe que o juros as vezes pode adicionar centavos no total e isso vai simular uma transação recusada (parece besta, mas muita gente quer simular pagamentos aprovados e acaba forçando todos para serem recusados por causa disso).

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* WooCommerce version 2.0.x or latter installed and active.
* Follow our [installation guide](http://wordpress.org/plugins/cielo-woocommerce/installation/).

### FAQ em Português: ###

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* WooCommerce versão 2.0.x ou superior instalado e ativado.
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

= Quais são os modelos de BuyPage disponíveis? =

* Cielo
* Loja (ainda não implementado, será possível em breve)

= Como funciona a BuyPage Cielo? =

Na BuyPage Cielo a digitação dos dados do cartão será no ambiente da Cielo. Geralmente é aconselhada a clientes que não queiram lidar com questões de segurança e desejam utilizar a infraestrutura da Cielo.

= Como funciona a BuyPage Loja? =

Na BuyPage Loja a digitação dos dados do cartão será no ambiente da Loja. Ou seja, a própria Loja se encarrega do desenvolvimento de uma página segura na Internet (HTTPS), respeitando políticas de segurança, para capturar os dados do cartão do portador.

Este modelo de BuyPage não esta disponível ainda no plugin, iremos implementar em breve e contamos com a sua ajuda, tanto para a melhoria do código e implementação desta feature como também para doações (desta forma você ajuda os desenvolvedores deste plugin a ter mais tempo livre para implementar esta funcionalidade).

= Ao pagar o pedido fica com o status "processando", isto esta certo ? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= É possível cancelar o pagamento/devolver o dinheiro do cliente pelo plugin? =

Sim, isto é possível mudando o status do pedido para "reembolsado", desta forma é enviado um sinal para a Cielo dizendo que a transação deve ser cancelada.

* Nota 1: Apenas pagamentos feitos em menos de 90 dias podem ser reembolsados.
* Nota 2: Isto irá funcionar apenas para pagamentos feitos depois de instalada a versão 3.0.0 deste plugin.

= Aconteceu um erro, o que eu faço? =

Sempre que ocorrer um erro você deve ativar a opção de log do plugin e tentar simular o erro novamente, desta forma o erro será gravado no arquivo de log e você poderá saber o que aconteceu.

Não é um problema caso você não consiga entender o arquivo de log, pois você pode salvar o conteúdo dele utilizando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com) solicitar ajuda usando:

* O fórum de suporte do [WordPress](http://wordpress.org/support/plugin/cielo-woocommerce) (apenas em inglês).
* O fórum de suporte do [WordPress Brasil](http://br.forums.wordpress.org/forum/plugins-e-codigos) utilizando as tags "cielo" e "woocommerce".
* O nosso fórum de suporte no [GitHub](https://github.com/greguly/cielo-woocommerce/issues).

= O que fazer quando tentar finalizar a compra aparece a mensagem "Cielo: Um erro aconteceu ao processar o seu pagamento, por favor, tente novamente ou entre em contato para conseguir assistência" ? =

Esta mensagem geralmente irá aparecer quando o seu servidor tiver problemas para fazer a conexão com a Cielo. Mas é possível saber com certeza o que aconteceu de errado utilizando a opção de log do plugin como descrito na sessão acima.

== For Developers ==

= Criando um template personalizado para o formulário do checkout =

É possível alterar formulário e adicionar um customizado utilizando o filtro `wc_cielo_form_path`.

Exemplo de uso do filtro em um tema:

	function custom_wc_cielo_form_path( $path ) {
		return get_template_directory() . '/cielo-html-form.php';
	}
	add_filter( 'wc_cielo_form_path', 'custom_wc_cielo_form_path' );

Depois basta criar o arquivo `cielo-html-form.php` dentro do seu tema e escrever o HTML dele como você julgar melhor (utilize os modelos prontos no plugin dentro de `includes/views` para ter uma ideia de como deve ser os elementos e as variáveis que você pode utilizar).

Ao criar um formulário customizado pode ser boa ideia remover os scripts adicionados pelo plugin e você pode fazer da seguinte forma:

	function remove_wc_cielo_scripts() {
		wp_dequeue_style( 'wc-cielo-checkout-icons' );
		wp_dequeue_script( 'wc-cielo-checkout-icons' );
	}
	add_action( 'wp_enqueue_scripts', 'remove_wc_cielo_scripts', 1000 );


== Screenshots ==

1. Configurações do plugin.
2. Checkout com o formulário padrão.
2. Checkout com o formulário utilizando ícones dos cartões.

== Changelog ==

= 3.0.1 - 22/06/2014 =

* Permitido valores inferiores a R$ 5,00 para pagamento à vista em cartão de crédito.
* Quando cancelado o pagamento na tela da Cielo o cliente é redirecionado para o caralogo do WooCommerce.

= 3.0.0 - 22/06/2014 =

* Refatorado todo o código do plugin.
* Adicionado completo suporte para as versões 2.0.x e 2.1.x do WooCommerce
* Atualizada a versão da API da Cielo para 1.3.0.
* Adicionada ação de cancelar a transação (devolver o dinheiro) ao marcar o pedido como "reembolsado".
* Adicionada novas formas de exibir o formulário com os cartões de crédito e parcelas disponíveis.
* Removida a opção de captura (não tinha utilidade e a melhor forma de trabalhar é capturar automaticamente sempre).
* Adicionado suporte para os cartões JBC e Aura.
* Adicionado opção de pagamento por débito para o MasterCard.
* Adicionada uma mensagem informando o cartão, forma de pagamento (crédito ou débito) e quantidade de parcelas nas notas do pedido ao concluir o pagamento.
* Adicionado filtro `wc_cielo_form_path`, que torna possível customizar o formulário de seleção do cartão e de parcelas.

= 2.0.10 - 17/06/2014 =

* Corrigido a finalização e a URL de retorno para versões 2.0.x do WooCommerce.

= 2.0.9 - 12/06/2014 =

* Correção dos valores padrões das opções.

= 2.0.8 - 12/06/2014 =

* Correção do retorno do pagamento para as versões 2.1.x do WooCommerce.

= 2.0.7 - 11/06/2014 =

* Suporte inicial para as versões 2.1.x do WooCommerce.

= 2.0.5 =

* Adicionadas mensagens de erro para quando as bandeiras de cartão não foram selecionadas.
* Removido o template customizado review-order.php

= 2.0.4 =

* Adicionada opção de reduzir o estoque quando o pagamento é completado com sucesso.

= 2.0.3 =

* Corrigida falha com a página de retorno da Cielo.

= 2.0.2 =

* Fixed a bug with as_is()/PHP < 5.3 where it hang at payment page.
* Added a direct settings link on plugins page list.

= 2.0.1 =

* Fixed a bug, thanks for Claudio Sanches who reported it.

= 2.0 =

* Updated to WooCommerce 2.0

= 1.0 =

* Initial plugin release.

== Upgrade Notice ==

= 3.0.1 =

* Refatorado todo o código do plugin.
* Adicionado completo suporte para as versões 2.0.x e 2.1.x do WooCommerce
* Atualizada a versão da API da Cielo para 1.3.0.
* Adicionada ação de cancelar a transação (devolver o dinheiro) ao marcar o pedido como "reembolsado".
* Adicionada novas formas de exibir o formulário com os cartões de crédito e parcelas disponíveis.
* Removida a opção de captura (não tinha utilidade e a melhor forma de trabalhar é capturar automaticamente sempre).
* Adicionado suporte para os cartões JBC e Aura.
* Adicionado opção de pagamento por débito para o MasterCard.
* Adicionada uma mensagem informando o cartão, forma de pagamento (crédito ou débito) e quantidade de parcelas nas notas do pedido ao concluir o pagamento.
* Adicionado filtro wc_cielo_form_path, que torna possível customizar o formulário de seleção do cartão e de parcelas.
* Permitido valores inferiores a R$ 5,00 para pagamento à vista em cartão de crédito.
* Quando cancelado o pagamento na tela da Cielo o cliente é redirecionado para o caralogo do WooCommerce.

== License ==

Cielo WooCommerce is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Cielo WooCommerce is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Cielo WooCommerce. If not, see <http://www.gnu.org/licenses/>.
