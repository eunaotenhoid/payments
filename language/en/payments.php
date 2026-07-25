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
    'ACP_EUNAUMTENHOID_PAYMENTS_TITLE'          => 'Payments Gateway',
    'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS'       => 'Payment Settings',
    'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS_EXPLAIN'=> 'Configure the credentials (APIs) for the payment methods your extensions will use.',
    'ENABLE_GATEWAY'            => 'Enable this payment method?',
    
    'MERCADOPAGO'               => 'Mercado Pago',
    'MERCADOPAGO_PUBLIC_KEY'    => 'Public Key',
    'MERCADOPAGO_TOKEN'         => 'Access Token',
    'MERCADOPAGO_TOKEN_EXPLAIN' => 'Enter the production or sandbox Access Token generated in the Mercado Pago dashboard.',
    
    'STRIPE'                    => 'Stripe',
    'STRIPE_KEY'                => 'Publishable Key',
    'STRIPE_SECRET'             => 'Secret Key',
    
    'PAYPAL'                    => 'PayPal',
    'PAYPAL_CLIENT'             => 'Client ID',
    'PAYPAL_SECRET'             => 'Client Secret',
    
    'COINBASE'                  => 'Coinbase Commerce',
    'COINBASE_KEY'              => 'API Key',
]);
