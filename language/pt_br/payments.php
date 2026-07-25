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
    'ACP_EUNAUMTENHOID_PAYMENTS_TITLE'          => 'Gateway de Pagamentos',
    'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS'       => 'Configurações de Pagamento',
    'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS_EXPLAIN'=> 'Configure aqui as credenciais (APIs) dos métodos de pagamento que as suas extensões utilizarão.',
    
    'ENABLE_GATEWAY'            => 'Ativar este meio de pagamento?',
    
    'MERCADOPAGO'               => 'Mercado Pago',
    'MERCADOPAGO_PUBLIC_KEY'    => 'Public Key',
    'MERCADOPAGO_TOKEN'         => 'Access Token',
    'MERCADOPAGO_TOKEN_EXPLAIN' => 'Insira o Access Token de produção ou testes gerado no painel do Mercado Pago.',
    
    'STRIPE'                    => 'Stripe',
    'STRIPE_KEY'                => 'Publishable Key',
    'STRIPE_SECRET'             => 'Secret Key',
    
    'PAYPAL'                    => 'PayPal',
    'PAYPAL_CLIENT'             => 'Client ID',
    'PAYPAL_SECRET'             => 'Client Secret',
    
    'COINBASE'                  => 'Coinbase Commerce',
    'COINBASE_KEY'              => 'API Key',
]);
