# Documentação para Desenvolvedores (API)
Extensão: `eunaumtenhoid/payments`

Esta extensão atua como um **Motor Central de Pagamentos** para o phpBB. Ela centraliza as chaves de API e a lógica de comunicação com os bancos/gateways.

Se você está construindo uma extensão que precisa cobrar o usuário (ex: Doações, Marketplace, Sistema de VIPs/Créditos), **você não precisa implementar lógicas de gateway do zero**. Basta enviar um pacote de dados para esta extensão, e ela devolverá o link de pagamento pronto para o usuário.

---

## 1. Injeção de Dependência (Dependency Injection)

Para usar a API de pagamentos, a sua extensão precisa injetar o serviço `@eunaumtenhoid.payments.manager` no seu controller.

No arquivo `config/services.yml` da **SUA** extensão, adicione:

```yaml
services:
    sua_vendor.sua_extensao.controller:
        class: sua_vendor\sua_extensao\controller\main
        arguments:
            - '@eunaumtenhoid.payments.manager'
```

No construtor da classe da sua extensão:

```php
protected $payment_manager;

public function __construct(\eunaumtenhoid\payments\payment\manager $payment_manager)
{
    $this->payment_manager = $payment_manager;
}
```

---

## 2. Criando um Pagamento (Create Payment)

Quando o usuário clicar em "Comprar", chame o método `create_payment` passando o nome do gateway desejado.

### Gateways Suportados
* `mercadopago` (Pix, Boleto, Cartão)
* `stripe` (Cartão, Apple/Google Pay)
* `paypal` (Saldo, Cartão)
* `coinbase` (Cripto - USDC/Base)
* `btcpay` (Bitcoin Nativamente)
* `coinpayments` (Altcoins)

### Exemplo de Uso:

```php
try {
    // Retorna um Array com os dados do checkout
    $checkout_data = $this->payment_manager->create_payment([
        'provider'    => 'mercadopago', // Qual gateway usar
        'amount'      => 5000,          // Valor em centavos (5000 = R$ 50,00)
        'currency'    => 'BRL',         // BRL, USD, EUR
        'description' => $this->language->lang('YOUR_EXT_PURCHASE_DESCRIPTION'),
        'return_url'  => $this->controller_helper->route('your_vendor_your_ext_success', [], true) // Gere a URL absoluta da sua rota
    ]);
    
    $transaction_id = $checkout_data['transaction_id']; // Salve isso no banco de dados da sua extensão!
    $checkout_url = $checkout_data['url'];             // Redirecione o usuario pra cá ou abra num iframe
    
    // Redireciona o usuário para o pagamento
    return new \Symfony\Component\HttpFoundation\RedirectResponse($checkout_url);

} catch (\Exception $e) {
    echo $this->language->lang('YOUR_EXT_PAYMENT_ERROR') . ': ' . $e->getMessage();
}
```

---

## 3. Verificando o Status (Verify Payment)

Após o usuário retornar para o seu fórum, você precisa verificar se o pagamento foi realmente aprovado antes de liberar o benefício (VIP, Créditos, etc).

Pegue o `$transaction_id` que você salvou no passo anterior e chame o método `verify_payment`.

```php
try {
    $result = $this->payment_manager->verify_payment('mercadopago', $transaction_id_salvo);

    if ($result['status'] === 'paid') {
        // Sucesso! O pagamento caiu. Libere o VIP do usuário aqui.
        echo $this->language->lang('YOUR_EXT_PAYMENT_APPROVED');
    } elseif ($result['status'] === 'pending') {
        echo $this->language->lang('YOUR_EXT_PAYMENT_PENDING');
    } else {
        echo $this->language->lang('YOUR_EXT_PAYMENT_FAILED');
    }

} catch (\Exception $e) {
    echo $this->language->lang('YOUR_EXT_VERIFICATION_ERROR') . ': ' . $e->getMessage();
}
```

---

## Dicas Adicionais

* **Modals e Iframes:** As URLs geradas por gatewas de cripto (`btcpay`, `coinbase`) funcionam muito bem dentro de iframes (janelas flutuantes). Se a sua extensão quiser uma experiência moderna, insira a URL devolvida em um modal HTML em vez de redirecionar a página.
* **Moeda:** A API aceita códigos fiat normais (BRL, USD). O provedor de cripto faz a conversão do valor fiduciário exigido (ex: R$ 50) para a fração de moeda digital correspondente na hora de abrir a fatura para o usuário.
