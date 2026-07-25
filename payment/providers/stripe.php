<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment\providers;

use eunaumtenhoid\payments\payment\provider_interface;

class stripe implements provider_interface
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\language\language */
    protected $language;

    public function __construct(\phpbb\config\config $config, \phpbb\language\language $language)
    {
        $this->config = $config;
        $this->language = $language;
    }

    public function get_name()
    {
        return 'stripe';
    }

    public function get_display_name()
    {
        return 'Stripe';
    }

    public function create_payment(array $payment_data)
    {
        // Define a chave secreta do painel ACP
        $secret_key = $this->config['eunaumtenhoid_payments_stripe_secret'];
        if (empty($secret_key)) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_STRIPE_MISSING_KEY'));
        }

        \Stripe\Stripe::setApiKey($secret_key);

        try {
            // Cria uma Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'], // Adicionamos 'card' de volta pois a conta do usuário não tem configurações dinâmicas de moeda ainda.
                'line_items' => [[
                    'price_data' => [
                        'currency' => $payment_data['currency'] ?? 'brl',
                        'product_data' => [
                            'name' => $payment_data['description'] ?? 'Pagamento Fórum',
                        ],
                        'unit_amount' => $payment_data['amount'], // O valor em centavos
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $payment_data['return_url'] . (strpos($payment_data['return_url'], '?') !== false ? '&' : '?') . 'status=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $payment_data['return_url'] . (strpos($payment_data['return_url'], '?') !== false ? '&' : '?') . 'status=cancelled',
                'metadata' => [
                    'extension_caller' => $payment_data['extension_caller'] ?? '',
                ],
            ]);

            return [
                'status' => 'pending',
                'transaction_id' => $session->id,
                'url' => $session->url,
            ];
        } catch (\Exception $e) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_STRIPE_ERROR') . ': ' . $e->getMessage());
        }
    }

    public function verify_payment($transaction_id)
    {
        $secret_key = $this->config['eunaumtenhoid_payments_stripe_secret'];
        \Stripe\Stripe::setApiKey($secret_key);

        try {
            $session = \Stripe\Checkout\Session::retrieve($transaction_id);
            $status = 'pending';

            if ($session->payment_status === 'paid') {
                $status = 'paid';
            } elseif ($session->payment_status === 'unpaid' && $session->status === 'expired') {
                $status = 'failed';
            }

            return [
                'status' => $status
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
