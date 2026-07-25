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
    'ACP_EUNAUMTENHOID_PAYMENTS_TITLE'    => 'Payments',
    'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS' => 'Gateway Settings',
    'ACP_EUNAUMTENHOID_PAYMENTS_DEVELOPERS' => 'Developers',

    // New keys for providers and ACP
    'ACP_EUNAUMTENHOID_PAYMENTS_PROVIDER_NOT_FOUND'    => 'Payment provider \'%s\' not found.',
    'ACP_EUNAUMTENHOID_PAYMENTS_STRIPE_MISSING_KEY'    => 'Stripe secret key is not configured.',
    'ACP_EUNAUMTENHOID_PAYMENTS_STRIPE_ERROR'          => 'Error processing Stripe',
    'ACP_EUNAUMTENHOID_PAYMENTS_PAYPAL_MISSING_CREDS'  => 'PayPal credentials are not configured in ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_PAYPAL_ERROR'          => 'Error processing PayPal',
    'ACP_EUNAUMTENHOID_PAYMENTS_MP_MISSING_TOKEN'      => 'Mercado Pago Token is not configured.',
    'ACP_EUNAUMTENHOID_PAYMENTS_MP_API_ERROR'          => 'Mercado Pago API Error',
    'ACP_EUNAUMTENHOID_PAYMENTS_MP_ERROR'              => 'Error processing Mercado Pago',
    'ACP_EUNAUMTENHOID_PAYMENTS_CP_MISSING_KEYS'       => 'CoinPayments API keys are not configured in ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_CP_ERROR'              => 'Error processing CoinPayments',
    'ACP_EUNAUMTENHOID_PAYMENTS_COINBASE_MISSING_KEY'  => 'Coinbase API key is not configured in ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_COINBASE_ERROR'        => 'Error processing Coinbase Commerce',
    'ACP_EUNAUMTENHOID_PAYMENTS_BTCPAY_MISSING_CONFIG' => 'BTCPay Server settings (URL, Store ID or API Key) are missing in ACP.',
    'ACP_EUNAUMTENHOID_PAYMENTS_BTCPAY_ERROR'          => 'Error processing BTCPay Server',
    'ACP_EUNAUMTENHOID_PAYMENTS_SAVED_SUCCESSFULLY'    => 'Settings saved successfully.',
]);
