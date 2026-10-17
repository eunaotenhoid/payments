<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment\providers;

use eunaumtenhoid\payments\payment\provider_interface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class coinpayments implements provider_interface
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\language\language */
    protected $language;

    /** @var \phpbb\user|null */
    protected $user;

    /** @var string Base URL for CoinPayments REST API v2 */
    protected $base_url = 'https://a-api.coinpayments.net/api/v2';

    public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\user $user = null)
    {
        $this->config = $config;
        $this->language = $language;
        $this->user = $user;
    }

    public function get_name()
    {
        return 'coinpayments';
    }

    public function get_display_name()
    {
        return 'CoinPayments (Altcoins)';
    }

    public function create_payment(array $payment_data)
    {
        $client_id = !empty($this->config['eunaumtenhoid_payments_coinpayments_client_id'])
            ? $this->config['eunaumtenhoid_payments_coinpayments_client_id']
            : ($this->config['eunaumtenhoid_payments_coinpayments_pub_key'] ?? '');

        $client_secret = !empty($this->config['eunaumtenhoid_payments_coinpayments_client_secret'])
            ? $this->config['eunaumtenhoid_payments_coinpayments_client_secret']
            : ($this->config['eunaumtenhoid_payments_coinpayments_priv_key'] ?? '');

        if (empty($client_id) || empty($client_secret)) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_CP_MISSING_KEYS'));
        }

        $client = new Client();

        // Valor formatado em decimal string (ex: "50.00")
        $amount_str = number_format($payment_data['amount'] / 100, 2, '.', '');
        $currency = strtoupper($payment_data['currency'] ?? 'BRL');

        // Dados do comprador
        $buyer_email = !empty($payment_data['buyer_email'])
            ? $payment_data['buyer_email']
            : (($this->user && !empty($this->user->data['user_email'])) ? $this->user->data['user_email'] : '');

        $buyer_name = ($this->user && !empty($this->user->data['username'])) ? $this->user->data['username'] : 'Buyer';

        // Constrói payload da fatura (v2 /merchant/invoices)
        // Nota: Omitir o objeto payment permite que o cliente escolha sua cripto preferida na tela de checkout CoinPayments
        $payload = [
            'currency' => $currency,
            'items'    => [
                [
                    'name'     => mb_substr($payment_data['description'] ?? 'Pagamento', 0, 100),
                    'amount'   => $amount_str,
                    'quantity' => [
                        'value' => 1,
                        'type'  => 'quantity'
                    ]
                ]
            ],
            'amount'   => [
                'breakdown' => [
                    'subtotal' => $amount_str
                ],
                'total'     => $amount_str
            ]
        ];

        if (!empty($buyer_email)) {
            $payload['buyer'] = [
                'name'         => [
                    'firstName' => $buyer_name,
                    'lastName'  => ''
                ],
                'emailAddress' => $buyer_email
            ];
        }

        // URLs de retorno se informadas (CoinPayments exige formato FQDN com ponto ou IP válido; 'localhost' puro é rejeitado)
        if (!empty($payment_data['return_url'])) {
            $return_url = $payment_data['return_url'];
            $parsed_host = parse_url($return_url, PHP_URL_HOST);

            // Em ambiente local 'localhost', converte para '127.0.0.1' para atender à validação de FQDN/IP da CoinPayments
            if ($parsed_host === 'localhost') {
                $return_url = preg_replace('#://localhost(?=[:/]|\z)#i', '://127.0.0.1', $return_url);
                $parsed_host = '127.0.0.1';
            }

            // Apenas adiciona se for um host com ponto (ex: dominio.com ou 127.0.0.1)
            if (!empty($parsed_host) && strpos($parsed_host, '.') !== false) {
                $url_com_status = $return_url . (strpos($return_url, '?') !== false ? '&' : '?') . 'status=';
                $payload['successUrl'] = $url_com_status . 'success';
                $payload['cancelUrl'] = $url_com_status . 'cancelled';
            }
        }

        $url = $this->base_url . '/merchant/invoices';
        $iso_date = gmdate('Y-m-d\TH:i:s');
        $payload_json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        // Assinatura oficial CoinPayments API v2: HMAC-SHA256 em Base64
        // Canonical string: \ufeff + Method + URL + ClientId + Timestamp + Payload
        $message = "\xEF\xBB\xBF" . 'POST' . $url . $client_id . $iso_date . $payload_json;
        $signature = base64_encode(hash_hmac('sha256', $message, $client_secret, true));

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'X-CoinPayments-Client'    => $client_id,
                    'X-CoinPayments-Timestamp' => $iso_date,
                    'X-CoinPayments-Signature' => $signature,
                    'Content-Type'             => 'application/json',
                    'Accept'                   => 'application/json',
                ],
                'body'    => $payload_json,
                'timeout' => 20
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // A API v2 retorna o objeto criado dentro de invoices: {"invoices": [{"id": "...", "checkoutLink": "..."}]}
            $invoice_data = !empty($body['invoices'][0]) ? $body['invoices'][0] : (!empty($body[0]) ? $body[0] : $body);
            $checkout_url = $invoice_data['checkoutLink'] ?? ($invoice_data['link'] ?? ($body['checkoutLink'] ?? ($body['link'] ?? '')));
            $invoice_id = $invoice_data['id'] ?? ($invoice_data['invoiceId'] ?? ($body['id'] ?? ($body['invoiceId'] ?? '')));

            if (empty($checkout_url) || empty($invoice_id)) {
                $err_msg = $body['message'] ?? ($this->language->is_set('PAYMENTS_ERROR_NO_URL') ? $this->language->lang('PAYMENTS_ERROR_NO_URL') : 'Erro ao obter URL de checkout da CoinPayments.');
                throw new \Exception($err_msg);
            }

            return [
                'status'         => 'pending',
                'transaction_id' => (string)$invoice_id,
                'url'            => $checkout_url,
            ];
        } catch (RequestException $e) {
            $error_details = '';
            if ($e->hasResponse()) {
                $err_body = json_decode($e->getResponse()->getBody()->getContents(), true);
                if (!empty($err_body['errors']) && is_array($err_body['errors'])) {
                    $messages = [];
                    foreach ($err_body['errors'] as $field => $errs) {
                        $messages[] = is_array($errs) ? implode(', ', $errs) : (string)$errs;
                    }
                    $error_details = implode('; ', $messages);
                } else {
                    $error_details = $err_body['detail'] ?? ($err_body['message'] ?? ($err_body['error'] ?? $e->getMessage()));
                }
            } else {
                $error_details = $e->getMessage();
            }
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_CP_ERROR') . ': ' . $error_details);
        } catch (\Exception $e) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_CP_ERROR') . ': ' . $e->getMessage());
        }
    }

    public function verify_payment($transaction_id)
    {
        $client_id = !empty($this->config['eunaumtenhoid_payments_coinpayments_client_id'])
            ? $this->config['eunaumtenhoid_payments_coinpayments_client_id']
            : ($this->config['eunaumtenhoid_payments_coinpayments_pub_key'] ?? '');

        $client_secret = !empty($this->config['eunaumtenhoid_payments_coinpayments_client_secret'])
            ? $this->config['eunaumtenhoid_payments_coinpayments_client_secret']
            : ($this->config['eunaumtenhoid_payments_coinpayments_priv_key'] ?? '');

        if (empty($client_id) || empty($client_secret)) {
            return ['status' => 'error', 'message' => 'Credenciais CoinPayments não configuradas'];
        }

        $client = new Client();
        $url = $this->base_url . '/merchant/invoices/' . rawurlencode($transaction_id);
        $iso_date = gmdate('Y-m-d\TH:i:s');

        // Para requisição GET sem body, o payload na assinatura é string vazia
        $message = "\xEF\xBB\xBF" . 'GET' . $url . $client_id . $iso_date . '';
        $signature = base64_encode(hash_hmac('sha256', $message, $client_secret, true));

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-CoinPayments-Client'    => $client_id,
                    'X-CoinPayments-Timestamp' => $iso_date,
                    'X-CoinPayments-Signature' => $signature,
                    'Accept'                   => 'application/json',
                ],
                'timeout' => 20
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $invoice_status = strtolower($body['status'] ?? '');

            // Status no ciclo de vida v2 da CoinPayments:
            // Sucesso: 'paid' (confirmado) ou 'completed' (creditado no saldo)
            // Falha: 'cancelled', 'timedout', 'deleted'
            // Em andamento: 'unpaid', 'pending', 'scheduled', 'draft'
            if (in_array($invoice_status, ['completed', 'paid'], true)) {
                $status = 'paid';
            } elseif (in_array($invoice_status, ['cancelled', 'timedout', 'deleted'], true)) {
                $status = 'failed';
            } else {
                $status = 'pending';
            }

            return [
                'status' => $status
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
