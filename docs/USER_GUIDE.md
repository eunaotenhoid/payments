# Guia de Configuração e Uso dos Meios de Pagamento
Extensão: `eunaumtenhoid/payments` (v1.1.0)

Este guia prático foi criado para administradores de fóruns phpBB. Ele explica como obter as credenciais e configurar passo a passo cada um dos gateways de pagamento suportados pela extensão **Central Payments**.

---

## Índice

1. [Visão Geral e Ativação](#1-visão-geral-e-ativação)
2. [Mercado Pago (Pix, Cartão, Boleto)](#2-mercado-pago)
3. [Stripe (Cartões Internacionais, Apple Pay, Google Pay)](#3-stripe)
4. [PayPal (Saldo e Cartões)](#4-paypal)
5. [Coinbase Commerce (Criptomoedas / Web3)](#5-coinbase-commerce)
6. [BTCPay Server (Bitcoin Autocustódia & Lightning)](#6-btcpay-server)
7. [CoinPayments (Altcoins & Tokens)](#7-coinpayments)
8. [Boas Práticas de Segurança e Produção](#8-boas-práticas-de-segurança-e-produção)
9. [Integração com outras extensões](#9-integração-com-outras-extensões)

---

## 1. Visão Geral e Ativação

A extensão **Central Payments** centraliza todas as configurações de pagamento do fórum em um único local:
* Acesse o **PCA (Painel de Controle da Administração)**.
* Vá para a aba **Extensões** > **Configurações de Pagamentos** (`ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS`).
* Você pode ativar apenas os gateways que desejar. Os gateways ativados ficarão disponíveis automaticamente para os usuários na tela de Checkout unificada.

> [!TIP]
> Você pode habilitar múltiplos gateways simultaneamente. Na tela de checkout do fórum, o usuário poderá escolher livremente entre eles (por exemplo, escolher entre Pix via Mercado Pago, Cartão via Stripe ou Bitcoin via BTCPay Server).

---

## 2. Mercado Pago

Ideal para comunidades brasileiras e latino-americanas, com suporte a **Pix instantâneo**, Cartão de Crédito e Boleto.

### Como obter as credenciais:
1. Acesse o portal de desenvolvedores do Mercado Pago: [https://www.mercadopago.com.br/developers](https://www.mercadopago.com.br/developers) (ou a versão do seu país).
2. Faça login com a sua conta do Mercado Pago / Mercado Livre.
3. Clique em **Suas integrações** e crie uma nova aplicação (ex: *"Fórum phpBB"*).
4. No menu lateral da sua aplicação, clique em **Credenciais de produção** (ou **Credenciais de teste** para homologação).
5. Copie os seguintes campos:
   * **Public Key** (começa com `APP_USR-...` ou `TEST-...`)
   * **Access Token** (começa com `APP_USR-...` ou `TEST-...`)

### Configuração no PCA do phpBB:
* **Habilitar Mercado Pago**: Selecione `Sim`.
* **Public Key (Chave Pública)**: Cole a sua Public Key.
* **Access Token (Token de Acesso)**: Cole o seu Access Token.
* Clique em **Enviar** para salvar.

---

## 3. Stripe

Ideal para aceitar cartões de crédito globais (Visa, Mastercard, American Express, Elo, etc.), carteiras digitais (Apple Pay e Google Pay) e moedas internacionais (BRL, USD, EUR, GBP).

### Como obter as credenciais:
1. Acesse o Dashboard do Stripe: [https://dashboard.stripe.com/](https://dashboard.stripe.com/).
2. No canto superior ou menu lateral esquerdo, clique em **Desenvolvedores (Developers)** > **Chaves de API (API Keys)**.
3. Localize as chaves padrão:
   * **Chave publicável (Publishable Key)**: Começa com `pk_live_...` (ou `pk_test_...`).
   * **Chave secreta (Secret Key)**: Clique em "Revelar chave" para copiar a chave que começa com `sk_live_...` (ou `sk_test_...`).

### Configuração no PCA do phpBB:
* **Habilitar Stripe**: Selecione `Sim`.
* **Publishable Key (Chave Publicável)**: Cole a chave `pk_...`.
* **Secret Key (Chave Secreta)**: Cole a chave `sk_...`.
* Clique em **Enviar** para salvar.

---

## 4. PayPal

Gateway reconhecido mundialmente para pagamentos com saldo da conta PayPal ou cartões internacionais.

### Como obter as credenciais:
1. Acesse o portal de desenvolvedores do PayPal: [https://developer.paypal.com/dashboard/applications](https://developer.paypal.com/dashboard/applications).
2. Faça login na sua conta comercial (Business).
3. Na seção **Apps & Credentials**, alterne para o modo **Live** (para pagamentos reais) ou **Sandbox** (para testes).
4. Clique em **Create App** e dê um nome à sua aplicação (ex: *"phpBB Payments"*).
5. Após criar o app, você verá:
   * **Client ID**
   * **Secret** (clique em *Show* para revelar)

### Configuração no PCA do phpBB:
* **Habilitar PayPal**: Selecione `Sim`.
* **Client ID**: Cole o Client ID gerado.
* **Client Secret**: Cole o Secret gerado.
* Clique em **Enviar** para salvar.

---

## 5. Coinbase Commerce

Permite que os usuários paguem utilizando criptomoedas com conversão automática ou checkout Web3 direto na blockchain (Bitcoin, Ethereum, USDC, Base, Polygon, Litecoin, Dogecoin).

### Como obter as credenciais:
1. Acesse o Coinbase Commerce: [https://commerce.coinbase.com/](https://commerce.coinbase.com/).
2. Faça login ou crie sua conta de comerciante.
3. No painel, vá em **Settings (Configurações)** > **Security (Segurança)**.
4. Rolar a página até a seção **API Keys**.
5. Clique em **New API Key (Criar Chave de API)** e copie o token gerado.

### Configuração no PCA do phpBB:
* **Habilitar Coinbase**: Selecione `Sim`.
* **API Key (Chave da API)**: Cole a chave obtida no painel da Coinbase.
* Clique em **Enviar** para salvar.

---

## 6. BTCPay Server

O **BTCPay Server** é a solução definitiva para quem busca **taxas zero de intermediação** e **autocustódia completa** de Bitcoin (On-chain e Lightning Network).

### Requisitos:
Uma instância BTCPay Server ativa (hospedada em servidor próprio ou provedor de nuvem).

### Como obter as credenciais:
1. Acesse sua instância do BTCPay Server e entre na sua **Loja (Store)**.
2. Anote o **Store ID**:
   * Ele fica visível nas configurações da loja (**Store Settings** > **General**) ou no final da URL do navegador.
3. Gerar a **API Key**:
   * Clique no seu perfil de usuário (canto superior direito ou menu lateral) > **Account** > **API Keys**.
   * Clique em **Generate Key**.
   * Conceda permissão para a sua loja criar faturas (`btcpay.store.cancreateinvoice`).
   * Gere a chave e copie o código secreto exibido.

### Configuração no PCA do phpBB:
* **Habilitar BTCPay Server**: Selecione `Sim`.
* **URL do Servidor**: Informe a URL base da sua instância (exemplo: `https://btcpay.meudominio.com`).
* **Store ID**: Cole o identificador da loja copiado no passo 2.
* **API Key**: Cole a chave de API gerada no passo 3.
* Clique em **Enviar** para salvar.

---

## 7. CoinPayments (API v2)

Gateway versátil para processamento de centenas de altcoins, moedas alternativas e stablecoins (USDT em diversas redes, BNB, Tron, Monero, etc.) utilizando a **Nova API REST v2**.

### Como obter as credenciais:
1. Acesse o Dashboard do CoinPayments: [https://dashboard.coinpayments.net](https://dashboard.coinpayments.net).
2. No menu lateral ou superior, acesse **Integrations (Integrações)** > **API Keys / Integrations**.
3. Crie uma nova integração e anote os dados gerados:
   * **Client ID** (identificador da integração).
   * **Client Secret** (chave secreta usada para assinar as requisições em HMAC-SHA256).
4. Certifique-se de que a integração tenha permissões ativas para emissão de faturas (`invoices`).

### Configuração no PCA do phpBB:
* **Habilitar CoinPayments**: Selecione `Sim`.
* **Client ID**: Cole o seu Client ID.
* **Client Secret**: Cole o seu Client Secret.
* Clique em **Enviar** para salvar.

---

## 8. Boas Práticas de Segurança e Produção

1. **Uso de HTTPS Obrigatório**:
   Todos os gateways de pagamento modernos (especialmente Stripe, PayPal e Mercado Pago) exigem conexões seguras sob SSL/HTTPS para tráfego de dados e redirecionamentos de checkout. Certifique-se de que o certificado SSL do seu fórum está válido.
2. **Sigilo de Chaves Secretas**:
   Nunca compartilhe suas chaves secretas (`sk_...`, `Access Token`, `Private Key`, `Client Secret`). Elas conferem acesso administrativo para cobranças e movimentações na sua conta do gateway.
3. **Ambiente de Testes (Sandbox)**:
   Antes de lançar campanhas ou vendas reais para sua comunidade, ative as credenciais de teste/sandbox do gateway desejado para simular uma transação completa até a tela de conclusão de checkout.
4. **Limpeza de Cache do phpBB**:
   Sempre que alterar arquivos ou configurações de template da extensão, limpe o cache do phpBB através do painel PCA (Aba Geral > Limpar o cache) para que as modificações entrem em vigor imediatamente.

---

## 9. Integração com outras extensões

Você é desenvolvedor ou deseja conectar sua extensão de Doações, VIPs ou Loja ao **Central Payments**?
Consulte a nossa documentação técnica dedicada:
* [`docs/DEVELOPERS.md`](DEVELOPERS.md) (Português)
* [`docs/DEVELOPERS.en.md`](DEVELOPERS.en.md) (English)
