<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\controller;

use eunaumtenhoid\payments\payment\manager;

class test
{
    /** @var \phpbb\template\template */
    protected $template;
    
    /** @var manager */
    protected $payment_manager;

    public function __construct(\phpbb\template\template $template, manager $payment_manager)
    {
        $this->template = $template;
        $this->payment_manager = $payment_manager;
    }

    public function handle($provider_name)
    {
        try {
            // Verifica se o provedor está habilitado e configurado
            $provider = $this->payment_manager->get_provider($provider_name);

            // Simula um pedido de doação de R$ 50 vindo de outra extensão
            $payment_data = [
                'amount'           => 5000, // 5000 centavos = R$ 50,00
                'currency'         => 'BRL',
                'description'      => 'Doação de Teste',
                'return_url'       => 'https://www.google.com/search?q=phpbb_test', 
                'extension_caller' => 'eunaumtenhoid/donations',
            ];

            // Chama a criação do pagamento na API real!
            $result = $provider->create_payment($payment_data);

            echo "<h1>Teste bem sucedido!</h1>";
            echo "<p>O provedor <strong>{$provider_name}</strong> comunicou-se perfeitamente com a API.</p>";
            echo "<p>ID da Transação: " . htmlspecialchars($result['transaction_id']) . "</p>";
            echo "<p><strong>Link do Checkout:</strong> <a href='" . htmlspecialchars($result['url']) . "' target='_blank'>" . htmlspecialchars($result['url']) . "</a></p>";
            exit;

        } catch (\Exception $e) {
            echo "<h1>Erro no Teste</h1>";
            echo "<p style='color:red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
            exit;
        }
    }
}
