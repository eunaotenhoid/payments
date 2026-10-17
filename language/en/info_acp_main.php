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
    
    // Checkout & Frontend
    'PAYMENTS_RETURN_TO'       => 'Complete and Return to %s',
    'PAYMENTS_RETURN_DEFAULT'  => 'Complete and Return',
    'PAYMENTS_STEP_REVISION'   => 'Review',
    'PAYMENTS_STEP_PAYMENT'    => 'Payment',
    'PAYMENTS_STEP_CONCLUSION' => 'Conclusion',
    'PAYMENTS_TOTAL_TO_PAY'    => 'Total to pay',
    'PAYMENTS_CHOOSE_METHOD'   => 'Choose payment method',
    'PAYMENTS_FINAL_SUBMIT'    => 'Complete Payment',
    'PAYMENTS_SECURE_ENV'      => '100% Secure & Encrypted Environment',
    'PAYMENTS_NO_GATEWAYS'     => 'No payment method is currently available.',
    'PAYMENTS_CONFIRMED_TITLE' => 'Payment Confirmed!',
    'PAYMENTS_DEFAULT_SUCCESS' => 'Your transaction was successfully processed and approved.',
    'PAYMENTS_PENDING_TITLE'   => 'Payment Pending / Under Review',
    'PAYMENTS_DEFAULT_PENDING' => 'Your invoice has been created and we are awaiting network/provider confirmation. Once confirmed, your transaction status will update automatically.',
    'PAYMENTS_FAILED_TITLE'    => 'Payment Incomplete',
    'PAYMENTS_DEFAULT_FAILED'  => 'The transaction was cancelled or could not be approved.',
    'PAYMENTS_DESC_LABEL'      => 'Description:',
    'PAYMENTS_STATUS_LABEL'    => 'Status:',
    'PAYMENTS_STATUS_APPROVED' => 'Approved',
    'PAYMENTS_STATUS_PENDING'  => 'Awaiting Payment / Confirmation',
    'PAYMENTS_TOTAL_PAID'      => 'Total Paid:',
    'PAYMENTS_BREADCRUMB_FINISH'    => 'Complete Payment',
    'PAYMENTS_BREADCRUMB_COMPLETED' => 'Payment Completed',
    'PAYMENTS_BREADCRUMB_FAILED'    => 'Payment Failed',
    'PAYMENTS_ERROR_NOT_FOUND'      => 'Invoice not found or expired.',
    'PAYMENTS_ERROR_NOT_ALLOWED'    => 'Payment method not allowed for this invoice.',
    'PAYMENTS_ERROR_NO_URL'         => 'The API did not return a checkout URL.',
    'PAYMENTS_SUB_STRIPE'      => 'Credit Cards (Visa, MasterCard, etc.)',
    'PAYMENTS_SUB_MERCADOPAGO' => 'Pay with Pix, Boleto or Card',
    'PAYMENTS_SUB_PAYPAL'      => 'Pay securely via PayPal',
    'PAYMENTS_SUB_CRYPTO'      => 'Bitcoin & Altcoins processed via Crypto',
    'PAYMENTS_SUB_DEFAULT'     => 'Secure encrypted payment processing',

    // Checkout Modal
    'PAYMENTS_MODAL_CRYPTO_TITLE'  => 'Cryptocurrency Payment',
    'PAYMENTS_MODAL_CRYPTO_NOTICE' => 'Choose your preferred coin on the screen below to view the address and QR Code.',
    'PAYMENTS_MODAL_CLOSE'         => 'Close',
    'PAYMENTS_MODAL_DONE'          => 'I have completed the payment',
    'PAYMENTS_MODAL_OPEN_TAB'      => 'Open invoice in a new tab',
    'PAYMENTS_PROCESSING'          => 'Processing...',

    // Logs
    'LOG_PAYMENTS_ERROR' => '<strong>Payments API Error (%1$s)</strong><br />» %2$s',
]);
