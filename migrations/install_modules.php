<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\migrations;

class install_modules extends \phpbb\db\migration\migration
{
    /**
     * This migration depends on the 'install' migration
     */
    static public function depends_on()
    {
        return ['\eunaumtenhoid\payments\migrations\install'];
    }

    /**
     * Define the data to insert (like ACP modules and config variables)
     */
    public function update_data()
    {
        return [
            // Add config variables for API Keys and toggles
            ['config.add', ['eunaumtenhoid_payments_mercadopago_enabled', '0']],
            ['config.add', ['eunaumtenhoid_payments_mercadopago_public_key', '']],
            ['config.add', ['eunaumtenhoid_payments_mercadopago_token', '']],
            ['config.add', ['eunaumtenhoid_payments_stripe_enabled', '0']],
            ['config.add', ['eunaumtenhoid_payments_stripe_key', '']],
            ['config.add', ['eunaumtenhoid_payments_stripe_secret', '']],
            ['config.add', ['eunaumtenhoid_payments_paypal_enabled', '0']],
            ['config.add', ['eunaumtenhoid_payments_paypal_client', '']],
            ['config.add', ['eunaumtenhoid_payments_paypal_secret', '']],
            ['config.add', ['eunaumtenhoid_payments_coinbase_enabled', '0']],
            ['config.add', ['eunaumtenhoid_payments_coinbase_key', '']],
            ['config.add', ['eunaumtenhoid_payments_btcpay_enabled', 0]],
            ['config.add', ['eunaumtenhoid_payments_btcpay_url', '']],
            ['config.add', ['eunaumtenhoid_payments_btcpay_store_id', '']],
            ['config.add', ['eunaumtenhoid_payments_btcpay_api_key', '']],
            ['config.add', ['eunaumtenhoid_payments_coinpayments_enabled', 0]],
            ['config.add', ['eunaumtenhoid_payments_coinpayments_pub_key', '']],
            ['config.add', ['eunaumtenhoid_payments_coinpayments_priv_key', '']],
            ['config.add', ['eunaumtenhoid_payments_coinpayments_default_coin', 'BTC']],

            // Add ACP Module Category
            ['module.add', [
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_EUNAUMTENHOID_PAYMENTS_TITLE'
            ]],
            
            // Add the Settings Module inside the category
            ['module.add', [
                'acp',
                'ACP_EUNAUMTENHOID_PAYMENTS_TITLE',
                [
                    'module_basename' => '\eunaumtenhoid\payments\acp\main_module',
                    'modes'           => ['settings'],
                ]
            ]],
        ];
    }
}
