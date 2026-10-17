# Documentação para Desenvolvedores (API)
Extensão: `eunaumtenhoid/payments` (v1.1.0)

Esta extensão atua como o **Motor Central e Unificado de Pagamentos** para o phpBB. Ela centraliza as credenciais dos gateways, a conformidade de segurança e a experiência do usuário com uma tela de **Checkout Padronizada em 3 Passos** (Revisão ➔ Pagamento ➔ Conclusão).

Se você está construindo uma extensão que precisa cobrar o usuário (ex: Doações, Marketplace, Sistema de VIPs/Créditos), **você não precisa implementar lógica de banco nem tela de checkout do zero**. Basta criar uma Fatura (*Intent*) e enviar o usuário para a nossa tela unificada.

---

## 1. Injeção de Dependência (Dependency Injection)

Para interagir com o Payments, injete o serviço `@eunaumtenhoid.payments.manager` no seu controller.

No arquivo `config/services.yml` da sua extensão:

```yaml
services:
    sua_vendor.sua_extensao.controller:
        class: sua_vendor\sua_extensao\controller\main
        arguments:
            # Utilize @? caso queira suporte opcional (soft dependency)
            - '@?eunaumtenhoid.payments.manager'
            - '@controller.helper'
```

No construtor da sua classe PHP:

```php
protected $payment_manager;
protected $helper;

public function __construct(
    \phpbb\controller\helper $helper,
    \eunaumtenhoid\payments\payment\manager $payment_manager = null
) {
    $this->helper = $helper;
    $this->payment_manager = $payment_manager;
}
```

---

## 2. Fluxo Recomendado: Checkout Centralizado (Passos 1, 2 e 3)

Em vez de redirecionar o usuário diretamente para um gateway específico, o fluxo padrão moderno utiliza o **Intent de Pagamento**.

### Passo 1: Criando a Fatura (`create_intent`)

Quando o usuário clicar em "Comprar" ou "Doar", empacote os dados da transação:

```php
try {
    $intent_id = $this->payment_manager->create_intent([
        'amount'           => 5000,                                 // Valor em centavos (5000 = R$ 50,00)
        'currency'         => 'BRL',                                // Moeda ISO (BRL, USD, EUR, etc.)
        'description'      => 'Assinatura VIP Ouro - Pedido #123',   // Descrição da cobrança
        'return_url'       => generate_board_url() . '/sua_ext/success?ref=123', // Rota de destino após a conclusão
        'return_text'      => 'ACP_SUA_EXT_TITLE',                  // Chave de idioma ou texto para o botão dinâmico de retorno
        'step1_title'      => 'SUA_EXT_STEP1_TITLE',                // (Opcional) Chave de idioma ou texto para o Passo 1 na barra de progresso (Padrão: 'Revisão')
        'reward_info'      => 'VIP Ouro',                           // (Opcional) Texto exibido abaixo da descrição no checkout
        'success_msg'      => 'Obrigado! Seu VIP foi liberado.',    // (Opcional) Mensagem na tela oficial de conclusão
        'cancel_msg'       => 'O pagamento não foi concluído.',     // (Opcional) Mensagem caso o usuário cancele
        'allowed_gateways' => ['mercadopago', 'stripe'],            // (Opcional) Filtrar gateways permitidos para esta compra
    ]);

    // Redireciona o usuário para a tela oficial de Checkout do Payments
    return new \Symfony\Component\HttpFoundation\RedirectResponse(
        $this->helper->route('eunaumtenhoid_payments_checkout', ['intent_id' => $intent_id])
    );

} catch (\Exception $e) {
    // Tratar erro ao gerar fatura
}
```

### O que o Payments faz automaticamente:
1. Exibe a tela de Checkout com o valor formatado, descrição e o badge com `reward_info` (se fornecido).
2. Mostra apenas os gateways habilitados no painel e permitidos pela sua fatura com seus respectivos ícones.
3. Processa a cobrança junto ao banco escolhido pelo usuário.
4. Redireciona para o **Passo 3 (Conclusão)** com a mensagem de sucesso ou cancelamento.
5. Renderiza o botão dinâmico: `Concluir e Retornar a [return_text] »`, redirecionando para a sua `return_url`.

---

## 3. Blindagem de Segurança e Validação de Retorno

**Nunca confie cegamente na URL** `?status=success`. Para evitar que usuários adulterem parâmetros no navegador, utilize um Token Criptográfico (HMAC) ou valide com a API:

### Exemplo com Token Criptográfico (HMAC):

Ao gerar a `return_url` no Passo 1:
```php
global $config;
$salt = $config['avatar_salt']; // Segredo interno do fórum
$security_token = hash_hmac('sha256', $pedido_id . '|' . $amount_cents, $salt);

$return_url = generate_board_url() . '/sua_ext/success?ref=' . $pedido_id . '&token=' . $security_token;
```

No método de retorno da sua extensão (`/sua_ext/success`):
```php
$status = $this->request->variable('status', '');
$provided_token = $this->request->variable('token', '');

$expected_token = hash_hmac('sha256', $pedido_id . '|' . $pedido['amount'], $config['avatar_salt']);

if (!hash_equals($expected_token, $provided_token)) {
    // Tentativa de fraude/manipulação de URL! Bloqueie.
    trigger_error('Assinatura de segurança inválida!');
}

if (in_array($status, ['success', 'approved', 'paid'])) {
    // Pagamento confirmado e autêntico! Libere os benefícios aqui.
}
```

---

## 4. Consultando Provedores Ativos no Painel (`get_providers`)

Se a sua extensão tiver configurações no ACP onde o administrador pode selecionar quais gateways serão aceitos (como na extensão de doações):

```php
// Retorna apenas os gateways ativados pelo admin no ACP do Payments:
$active_providers = $this->payment_manager->get_providers(true);

foreach ($active_providers as $name => $provider) {
    echo $provider->get_display_name(); // "Mercado Pago", "Stripe", etc.
}
```

---

## 5. Eventos do phpBB (Event Hooks)

A extensão Payments dispara eventos nativos do phpBB que permitem estender a lógica:

### `eunaumtenhoid.payments.checkout_completed`
Disparado ao carregar a tela oficial de conclusão (Passo 3).

* **Variáveis disponíveis no evento:**
  * `intent`: Array completo com todos os dados da fatura.
  * `is_success`: Booleano indicando se o status é positivo.
  * `status`: Código de status retornado pelo gateway (`success`, `approved`, `cancelled`, etc.).
  * `final_url`: URL de retorno para a extensão terceira.
  * `return_label`: Texto do botão de retorno.
  * `custom_msg`: Mensagem personalizada exibida na tela.

---

## 6. Modo Legado / Pulo Direto (`create_payment`)

Caso você não queira usar a tela de checkout unificada e prefira enviar o usuário diretamente para um gateway específico de sua escolha:

```php
$checkout_data = $this->payment_manager->create_payment('stripe', [
    'amount'      => 5000,
    'currency'    => 'BRL',
    'description' => 'Venda Direta',
    'return_url'  => generate_board_url() . '/sua_ext/success',
]);

return new \Symfony\Component\HttpFoundation\RedirectResponse($checkout_data['url']);
```
