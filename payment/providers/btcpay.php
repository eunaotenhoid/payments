<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment\providers;

use eunaumtenhoid\payments\payment\provider_interface;

class btcpay implements provider_interface
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
        return 'btcpay';
    }

    public function get_display_name()
    {
        return 'BTCPay Server';
    }

    public function create_payment(array $payment_data)
    {
        $server_url = rtrim($this->config['eunaumtenhoid_payments_btcpay_url'], '/');
        $store_id = $this->config['eunaumtenhoid_payments_btcpay_store_id'];
        $api_key = $this->config['eunaumtenhoid_payments_btcpay_api_key'];

        if (empty($server_url) || empty($store_id) || empty($api_key)) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_BTCPAY_MISSING_CONFIG'));
        }

        $client = new \GuzzleHttp\Client();
        
        // Formata para decimal string
        $amount_str = number_format($payment_data['amount'] / 100, 2, '.', '');
        
        $url_com_status = $payment_data['return_url'] . (strpos($payment_data['return_url'], '?') !== false ? '&' : '?') . 'status=';

        try {
            $response = $client->request('POST', $server_url . '/api/v1/stores/' . $store_id . '/invoices', [
                'headers' => [
                    'Authorization' => 'token ' . $api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => [
                    'amount' => $amount_str,
                    'currency' => strtoupper($payment_data['currency'] ?? 'BRL'),
                    'metadata' => [
                        'orderId' => $payment_data['description'] ?? 'Pedido',
                        'itemDesc' => $payment_data['description'] ?? 'Pagamento Fórum'
                    ],
                    'checkout' => [
                        'redirectURL' => $url_com_status . 'success' // O BTCPay vai redirecionar o usuário para cá quando pagar
                    ]
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'status' => 'pending',
                'transaction_id' => $body['id'],
                'url' => $body['checkoutLink'],
            ];
        } catch (\Exception $e) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_BTCPAY_ERROR') . ': ' . $e->getMessage());
        }
    }

    public function verify_payment($transaction_id)
    {
        $server_url = rtrim($this->config['eunaumtenhoid_payments_btcpay_url'], '/');
        $store_id = $this->config['eunaumtenhoid_payments_btcpay_store_id'];
        $api_key = $this->config['eunaumtenhoid_payments_btcpay_api_key'];

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $server_url . '/api/v1/stores/' . $store_id . '/invoices/' . $transaction_id, [
                'headers' => [
                    'Authorization' => 'token ' . $api_key,
                    'Accept'        => 'application/json',
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $status = 'pending';
            
            // BTCPay statuses: New, Processing, Settled, Invalid, Expired
            $invoice_status = $body['status'] ?? '';
            
            if ($invoice_status === 'Settled' || $invoice_status === 'Processing') {
                $status = 'paid'; // Pode ajustar a regra de negócio se Processing deve ser pending ainda
            } elseif ($invoice_status === 'Invalid' || $invoice_status === 'Expired') {
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
