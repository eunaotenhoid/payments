<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = [];
}

$lang = array_merge($lang, [
    'ACP_EUNAUMTENHOID_PAYMENTS_TITLE'    => 'Pagamentos',
    'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS' => 'Configurações de Gateways',
    'ACP_EUNAUMTENHOID_PAYMENTS_DEVELOPERS' => 'Desenvolvedores',

    // New keys for providers and ACP
    'ACP_EUNAUMTENHOID_PAYMENTS_PROVIDER_NOT_FOUND'    => 'Provedor de pagamento \'%s\' não encontrado.',
    'ACP_EUNAUMTENHOID_PAYMENTS_STRIPE_MISSING_KEY'    => 'A chave secreta do Stripe não foi configurada.',
    'ACP_EUNAUMTENHOID_PAYMENTS_STRIPE_ERROR'          => 'Erro ao processar Stripe',
    'ACP_EUNAUMTENHOID_PAYMENTS_PAYPAL_MISSING_CREDS'  => 'Credenciais do PayPal não configuradas no ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_PAYPAL_ERROR'          => 'Erro ao processar PayPal',
    'ACP_EUNAUMTENHOID_PAYMENTS_MP_MISSING_TOKEN'      => 'O Token do Mercado Pago não foi configurado.',
    'ACP_EUNAUMTENHOID_PAYMENTS_MP_API_ERROR'          => 'Erro na API do Mercado Pago',
    'ACP_EUNAUMTENHOID_PAYMENTS_MP_ERROR'              => 'Erro ao processar Mercado Pago',
    'ACP_EUNAUMTENHOID_PAYMENTS_CP_MISSING_KEYS'       => 'Chaves da API do CoinPayments não configuradas no ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_CP_ERROR'              => 'Erro ao processar CoinPayments',
    'ACP_EUNAUMTENHOID_PAYMENTS_COINBASE_MISSING_KEY'  => 'Chave da API da Coinbase não configurada no ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_COINBASE_ERROR'        => 'Erro ao processar Coinbase Commerce',
    'ACP_EUNAUMTENHOID_PAYMENTS_BTCPAY_MISSING_CONFIG' => 'Configurações do BTCPay Server (URL, Store ID ou API Key) estão faltando no painel ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_BTCPAY_ERROR'          => 'Erro ao processar BTCPay Server',
    'ACP_EUNAUMTENHOID_PAYMENTS_SAVED_SUCCESSFULLY'    => 'Configurações salvas com sucesso.',
    
    // Checkout & Frontend
    'PAYMENTS_RETURN_TO'       => 'Concluir e Retornar a %s',
    'PAYMENTS_RETURN_DEFAULT'  => 'Concluir e Retornar',
    'PAYMENTS_STEP_REVISION'   => 'Revisão',
    'PAYMENTS_STEP_PAYMENT'    => 'Pagamento',
    'PAYMENTS_STEP_CONCLUSION' => 'Conclusão',
    'PAYMENTS_TOTAL_TO_PAY'    => 'Total a pagar',
    'PAYMENTS_CHOOSE_METHOD'   => 'Escolha a forma de pagamento',
    'PAYMENTS_FINAL_SUBMIT'    => 'Finalizar Pagamento',
    'PAYMENTS_SECURE_ENV'      => 'Ambiente 100% Seguro e Criptografado',
    'PAYMENTS_NO_GATEWAYS'     => 'Nenhuma forma de pagamento está disponível no momento.',
    'PAYMENTS_CONFIRMED_TITLE' => 'Pagamento Confirmado!',
    'PAYMENTS_DEFAULT_SUCCESS' => 'Sua transação foi processada e aprovada com sucesso.',
    'PAYMENTS_PENDING_TITLE'   => 'Pagamento Pendente / Em Análise',
    'PAYMENTS_DEFAULT_PENDING' => 'Sua fatura foi gerada e estamos aguardando a confirmação da rede ou instituição financeira. Assim que for confirmada, o status será atualizado automaticamente.',
    'PAYMENTS_FAILED_TITLE'    => 'Pagamento Não Concluído',
    'PAYMENTS_DEFAULT_FAILED'  => 'A transação foi cancelada ou não pôde ser aprovada pela instituição.',
    'PAYMENTS_DESC_LABEL'      => 'Descrição:',
    'PAYMENTS_STATUS_LABEL'    => 'Status:',
    'PAYMENTS_STATUS_APPROVED' => 'Aprovado',
    'PAYMENTS_STATUS_PENDING'  => 'Aguardando Pagamento / Confirmação',
    'PAYMENTS_TOTAL_PAID'      => 'Total Pago:',
    'PAYMENTS_BREADCRUMB_FINISH'    => 'Finalizar Pagamento',
    'PAYMENTS_BREADCRUMB_COMPLETED' => 'Pagamento Concluído',
    'PAYMENTS_BREADCRUMB_FAILED'    => 'Falha no Pagamento',
    'PAYMENTS_ERROR_NOT_FOUND'      => 'Fatura não encontrada ou já expirada.',
    'PAYMENTS_ERROR_NOT_ALLOWED'    => 'Método de pagamento não permitido para esta fatura.',
    'PAYMENTS_ERROR_NO_URL'         => 'A API não retornou uma URL de checkout.',
    'PAYMENTS_SUB_STRIPE'      => 'Cartões de Crédito (Visa, Master, etc)',
    'PAYMENTS_SUB_MERCADOPAGO' => 'Pague com Pix, Boleto ou Cartão',
    'PAYMENTS_SUB_PAYPAL'      => 'Pague com segurança via PayPal',
    'PAYMENTS_SUB_CRYPTO'      => 'Bitcoin e Altcoins processado via Cripto',
    'PAYMENTS_SUB_DEFAULT'     => 'Processamento seguro criptografado',
    
    // Checkout Modal
    'PAYMENTS_MODAL_CRYPTO_TITLE'  => 'Pagamento em Criptomoeda',
    'PAYMENTS_MODAL_CRYPTO_NOTICE' => 'Escolha sua criptomoeda na tela abaixo para ver a carteira e o QR Code de transferência.',
    'PAYMENTS_MODAL_CLOSE'         => 'Fechar',
    'PAYMENTS_MODAL_DONE'          => 'Já realizei o pagamento',
    'PAYMENTS_MODAL_OPEN_TAB'      => 'Abrir fatura em nova aba',
    'PAYMENTS_PROCESSING'          => 'Processando...',

    // Logs
    'LOG_PAYMENTS_ERROR' => '<strong>Erro na API de Pagamentos (%1$s)</strong><br />» %2$s',
]);
