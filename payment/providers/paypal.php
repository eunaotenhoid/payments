<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment\providers;

use eunaumtenhoid\payments\payment\provider_interface;

class paypal implements provider_interface
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
        return 'paypal';
    }

    public function get_display_name()
    {
        return 'PayPal';
    }

    public function create_payment(array $payment_data)
    {
        $client_id = $this->config['eunaumtenhoid_payments_paypal_client'];
        $client_secret = $this->config['eunaumtenhoid_payments_paypal_secret'];

        if (empty($client_id) || empty($client_secret)) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_PAYPAL_MISSING_CREDS'));
        }

        // Você pode criar uma config "is_sandbox" depois, por enquanto assumimos ProductionEnvironment 
        // ou SandboxEnvironment dependendo da chave, aqui usamos sandbox como exemplo padrão ou production.
        // Vamos usar Sandbox por segurança até o usuário mudar.
        $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment($client_id, $client_secret);
        $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);

        $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        $request->prefer('return=representation');
        
        // O PayPal exige o formato "10.00" (string decimal)
        $amount_str = number_format($payment_data['amount'] / 100, 2, '.', '');

        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "ref_" . uniqid(),
                "description" => $payment_data['description'] ?? 'Pagamento',
                "amount" => [
                    "value" => $amount_str,
                    "currency_code" => strtoupper($payment_data['currency'] ?? 'BRL')
                ]
            ]],
            "application_context" => [
                "cancel_url" => $payment_data['return_url'] . (strpos($payment_data['return_url'], '?') !== false ? '&' : '?') . 'status=cancelled',
                "return_url" => $payment_data['return_url'] . (strpos($payment_data['return_url'], '?') !== false ? '&' : '?') . 'status=success'
            ] 
        ];

        try {
            $response = $client->execute($request);
            $approve_link = '';
            
            // Procura o link de aprovação que o usuário deve clicar
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    $approve_link = $link->href;
                    break;
                }
            }

            return [
                'status' => 'pending',
                'transaction_id' => $response->result->id,
                'url' => $approve_link,
            ];
        } catch (\Exception $e) {
            throw new \Exception($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_PAYPAL_ERROR') . ': ' . $e->getMessage());
        }
    }

    public function verify_payment($transaction_id)
    {
        $client_id = $this->config['eunaumtenhoid_payments_paypal_client'];
        $client_secret = $this->config['eunaumtenhoid_payments_paypal_secret'];
        
        $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment($client_id, $client_secret);
        $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);

        $request = new \PayPalCheckoutSdk\Orders\OrdersGetRequest($transaction_id);

        try {
            $response = $client->execute($request);
            $status = 'pending';

            if ($response->result->status === 'COMPLETED' || $response->result->status === 'APPROVED') {
                $status = 'paid';
            } elseif ($response->result->status === 'VOIDED') {
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
