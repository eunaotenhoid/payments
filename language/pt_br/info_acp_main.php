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
]);
