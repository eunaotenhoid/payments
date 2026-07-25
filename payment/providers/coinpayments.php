<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment\providers;

use eunaumtenhoid\payments\payment\provider_interface;

class coinpayments implements provider_interface
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
        return 'coinpayments';
    }

    public function get_display_name()
    {
        return 'CoinPayments';
    }

    public function create_payment(array $payment_data)
    {
        $public_key = $this->config['eunaumtenhoid_payments_coinpayments_pub_key'];
        $private_key = $this->config['eunaumtenhoid_payments_coinpayments_priv_key'];
        $default_crypto = $this->config['eunaumtenhoid_payments_coinpayments_default_coin'] ?: 'BTC';

        if (empty($public_key) || empty($private_key)) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_CP_MISSING_KEYS'));
        }

        $client = new \GuzzleHttp\Client();
        
        // Formata para decimal string
        $amount_str = number_format($payment_data['amount'] / 100, 2, '.', '');
        
        $url_com_status = $payment_data['return_url'] . (strpos($payment_data['return_url'], '?') !== false ? '&' : '?') . 'status=';

        $req = [
            'version'     => 1,
            'cmd'         => 'create_transaction',
            'amount'      => $amount_str,
            'currency1'   => strtoupper($payment_data['currency'] ?? 'BRL'),
            'currency2'   => strtoupper($default_crypto),
            'buyer_email' => 'comprador@forum.com', // Campo obrigatorio em algumas contas, entao passamos um fallback
            'item_name'   => $payment_data['description'] ?? 'Pagamento',
            'success_url' => $url_com_status . 'success',
            'cancel_url'  => $url_com_status . 'cancelled',
            'key'         => $public_key,
            'format'      => 'json'
        ];

        $post_data = http_build_query($req, '', '&');
        $hmac = hash_hmac('sha512', $post_data, $private_key);

        try {
            $response = $client->request('POST', 'https://www.coinpayments.net/api.php', [
                'headers' => [
                    'HMAC'         => $hmac,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $post_data
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($body['error'] !== 'ok') {
                throw new \Exception($body['error']);
            }

            return [
                'status' => 'pending',
                'transaction_id' => $body['result']['txn_id'],
                'url' => $body['result']['checkout_url'],
            ];
        } catch (\Exception $e) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_CP_ERROR') . ': ' . $e->getMessage());
        }
    }

    public function verify_payment($transaction_id)
    {
        $public_key = $this->config['eunaumtenhoid_payments_coinpayments_pub_key'];
        $private_key = $this->config['eunaumtenhoid_payments_coinpayments_priv_key'];

        $client = new \GuzzleHttp\Client();

        $req = [
            'version' => 1,
            'cmd'     => 'get_tx_info',
            'txid'    => $transaction_id,
            'key'     => $public_key,
            'format'  => 'json'
        ];

        $post_data = http_build_query($req, '', '&');
        $hmac = hash_hmac('sha512', $post_data, $private_key);

        try {
            $response = $client->request('POST', 'https://www.coinpayments.net/api.php', [
                'headers' => [
                    'HMAC'         => $hmac,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $post_data
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $status = 'pending';
            
            if ($body['error'] !== 'ok') {
                return ['status' => 'error', 'message' => $body['error']];
            }

            // CoinPayments status: >= 100 is complete, < 0 is failed/cancelled, 0 or positive is pending
            $cp_status = (int)$body['result']['status'];
            
            if ($cp_status >= 100) {
                $status = 'paid';
            } elseif ($cp_status < 0) {
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
