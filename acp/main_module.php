<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\acp;

class main_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $user, $template, $request, $config;

        $user->add_lang_ext('eunaumtenhoid/payments', 'payments');

        $this->tpl_name = 'acp_payments';
        $this->page_title = $user->lang('ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS');

        add_form_key('eunaumtenhoid_payments');

        if ($request->is_set_post('submit')) {
            if (!check_form_key('eunaumtenhoid_payments')) {
                trigger_error($user->lang('FORM_INVALID'), E_USER_WARNING);
            }

            $config->set('eunaumtenhoid_payments_mercadopago_enabled', $request->variable('mercadopago_enabled', 0));
            $config->set('eunaumtenhoid_payments_mercadopago_public_key', $request->variable('mercadopago_public_key', ''));
            $config->set('eunaumtenhoid_payments_mercadopago_token', $request->variable('mercadopago_token', ''));
            
            $config->set('eunaumtenhoid_payments_stripe_enabled', $request->variable('stripe_enabled', 0));
            $config->set('eunaumtenhoid_payments_stripe_key', $request->variable('stripe_key', ''));
            $config->set('eunaumtenhoid_payments_stripe_secret', $request->variable('stripe_secret', ''));
            
            $config->set('eunaumtenhoid_payments_paypal_enabled', $request->variable('paypal_enabled', 0));
            $config->set('eunaumtenhoid_payments_paypal_client', $request->variable('paypal_client', ''));
            $config->set('eunaumtenhoid_payments_paypal_secret', $request->variable('paypal_secret', ''));
            
            $config->set('eunaumtenhoid_payments_coinbase_enabled', $request->variable('coinbase_enabled', 0));
            $config->set('eunaumtenhoid_payments_coinbase_key', $request->variable('coinbase_key', ''));

            $config->set('eunaumtenhoid_payments_btcpay_enabled', $request->variable('btcpay_enabled', 0));
            $config->set('eunaumtenhoid_payments_btcpay_url', $request->variable('btcpay_url', ''));
            $config->set('eunaumtenhoid_payments_btcpay_store_id', $request->variable('btcpay_store_id', ''));
            $config->set('eunaumtenhoid_payments_btcpay_api_key', $request->variable('btcpay_api_key', ''));

            $config->set('eunaumtenhoid_payments_coinpayments_enabled', $request->variable('coinpayments_enabled', 0));
            $config->set('eunaumtenhoid_payments_coinpayments_pub_key', $request->variable('coinpayments_pub_key', ''));
            $config->set('eunaumtenhoid_payments_coinpayments_priv_key', $request->variable('coinpayments_priv_key', ''));
            $config->set('eunaumtenhoid_payments_coinpayments_default_coin', $request->variable('coinpayments_default_coin', 'BTC'));

            trigger_error($user->lang('ACP_EUNAUMTENHOID_PAYMENTS_SAVED_SUCCESSFULLY') . adm_back_link($this->u_action));
        }

        $template->assign_vars([
            'MERCADOPAGO_ENABLED'    => $config['eunaumtenhoid_payments_mercadopago_enabled'],
            'MERCADOPAGO_PUBLIC_KEY' => $config['eunaumtenhoid_payments_mercadopago_public_key'],
            'MERCADOPAGO_TOKEN'      => $config['eunaumtenhoid_payments_mercadopago_token'],
            
            'STRIPE_ENABLED'      => $config['eunaumtenhoid_payments_stripe_enabled'],
            'STRIPE_KEY'          => $config['eunaumtenhoid_payments_stripe_key'],
            'STRIPE_SECRET'       => $config['eunaumtenhoid_payments_stripe_secret'],
            
            'PAYPAL_ENABLED'      => $config['eunaumtenhoid_payments_paypal_enabled'],
            'PAYPAL_CLIENT'       => $config['eunaumtenhoid_payments_paypal_client'],
            'PAYPAL_SECRET'       => $config['eunaumtenhoid_payments_paypal_secret'],
            
            'COINBASE_ENABLED'    => $config['eunaumtenhoid_payments_coinbase_enabled'],
            'COINBASE_KEY'        => $config['eunaumtenhoid_payments_coinbase_key'],
            
            'BTCPAY_ENABLED'      => $config['eunaumtenhoid_payments_btcpay_enabled'] ?? 0,
            'BTCPAY_URL'          => $config['eunaumtenhoid_payments_btcpay_url'] ?? '',
            'BTCPAY_STORE_ID'     => $config['eunaumtenhoid_payments_btcpay_store_id'] ?? '',
            'BTCPAY_API_KEY'      => $config['eunaumtenhoid_payments_btcpay_api_key'] ?? '',
            
            'COINPAYMENTS_ENABLED'      => $config['eunaumtenhoid_payments_coinpayments_enabled'] ?? 0,
            'COINPAYMENTS_PUB_KEY'      => $config['eunaumtenhoid_payments_coinpayments_pub_key'] ?? '',
            'COINPAYMENTS_PRIV_KEY'     => $config['eunaumtenhoid_payments_coinpayments_priv_key'] ?? '',
            'COINPAYMENTS_DEFAULT_COIN' => $config['eunaumtenhoid_payments_coinpayments_default_coin'] ?? 'BTC',

            'U_ACTION'            => $this->u_action,
        ]);
    }
}
