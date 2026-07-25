<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment\providers;

use eunaumtenhoid\payments\payment\provider_interface;

class mercadopago implements provider_interface
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
        return 'mercadopago';
    }

    public function get_display_name()
    {
        return 'Mercado Pago (Cartão e PIX)';
    }

    public function create_payment(array $payment_data)
    {
        $token = $this->config['eunaumtenhoid_payments_mercadopago_token'];
        if (empty($token)) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_MP_MISSING_TOKEN'));
        }

        \MercadoPago\MercadoPagoConfig::setAccessToken($token);

        try {
            $client = new \MercadoPago\Client\Preference\PreferenceClient();
            
            // O manager deve passar amount_decimal (ex: 50.00) ou dividimos aqui
            $unit_price = (float) ($payment_data['amount'] / 100); 

            $safe_url = !empty($payment_data['return_url']) ? $payment_data['return_url'] : 'http://127.0.0.1';
            if (strpos($safe_url, 'http') !== 0) {
                $safe_url = 'http://' . ltrim($safe_url, '/');
            }

            $request = [
                "items" => [
                    [
                        "title" => $payment_data['description'] ?? 'Pagamento Fórum',
                        "quantity" => 1,
                        "unit_price" => $unit_price
                    ]
                ],
                "back_urls" => [
                    "success" => $safe_url . (strpos($safe_url, '?') !== false ? '&' : '?') . 'status=success',
                    "failure" => $safe_url . (strpos($safe_url, '?') !== false ? '&' : '?') . 'status=failed',
                    "pending" => $safe_url . (strpos($safe_url, '?') !== false ? '&' : '?') . 'status=pending'
                ],
                "auto_return" => "approved"
            ];

            $preference = $client->create($request);

            return [
                'status' => 'pending',
                'transaction_id' => $preference->id,
                'url' => $preference->init_point, // Link para redirecionar o usuário
            ];
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $response = $e->getApiResponse();
            $content = $response->getContent();
            $error_msg = $e->getMessage();
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_MP_API_ERROR') . ': ' . $error_msg . ' | Detalhes: ' . json_encode($content));
        } catch (\Exception $e) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_MP_ERROR') . ': ' . $e->getMessage());
        }
    }

    public function verify_payment($transaction_id)
    {
        $token = $this->config['eunaumtenhoid_payments_mercadopago_token'];
        \MercadoPago\MercadoPagoConfig::setAccessToken($token);

        try {
            $client = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get($transaction_id);
            
            $status = 'pending';

            if ($payment && $payment->status === 'approved') {
                $status = 'paid';
            } elseif ($payment && in_array($payment->status, ['rejected', 'cancelled'])) {
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
