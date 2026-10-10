<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class sandbox
{
    /** @var \phpbb\template\template */
    protected $template;

    /** @var \eunaumtenhoid\payments\payment\manager */
    protected $payment_manager;

    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\auth\auth */
    protected $auth;

    public function __construct(\phpbb\template\template $template, \eunaumtenhoid\payments\payment\manager $payment_manager, \phpbb\request\request $request, \phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\auth\auth $auth)
    {
        $this->template = $template;
        $this->payment_manager = $payment_manager;
        $this->request = $request;
        $this->helper = $helper;
        $this->user = $user;
        $this->auth = $auth;
    }

    public function handle()
    {
        if (!$this->auth->acl_get('a_')) {
            trigger_error('Acesso restrito a administradores.');
        }

        $amount = $this->request->variable('amount', 5000); // 5000 = 50.00
        
        // Gerar URL absoluta para os gateways que exigem (Stripe/PayPal)
        $return_url = generate_board_url() . '/app.php/payments/sandbox';

        // Se o usuário clicar em "Testar Tela Centralizada"
        if ($this->request->is_set_post('submit_intent')) {
            try {
                $intent_id = $this->payment_manager->create_intent([
                    'amount'      => $amount,
                    'currency'    => 'BRL',
                    'description' => 'Compra de Teste no Sandbox',
                    'return_url'  => $return_url
                ]);
                return new RedirectResponse($this->helper->route('eunaumtenhoid_payments_checkout', ['intent_id' => $intent_id]));
            } catch (\Exception $e) {
                $this->template->assign_var('SANDBOX_ERROR', $e->getMessage());
            }
        }

        // Se o usuário clicar no pulo direto antigo
        if ($this->request->is_set_post('submit_direct')) {
            $provider_name = $this->request->variable('provider', '');
            
            try {
                $data = $this->payment_manager->create_payment($provider_name, [
                    'amount'      => $amount,
                    'currency'    => 'BRL',
                    'description' => 'Pulo Direto via Sandbox',
                    'return_url'  => $return_url
                ]);

                if (isset($data['url']) && !empty($data['url'])) {
                    return new RedirectResponse($data['url']);
                } else {
                    $this->template->assign_var('SANDBOX_ERROR', 'A API não retornou uma URL de checkout.');
                }
            } catch (\Exception $e) {
                $this->template->assign_var('SANDBOX_ERROR', $e->getMessage());
            }
        }

        // Buscar provedores ativos
        $providers = $this->payment_manager->get_providers(true);
        foreach ($providers as $name => $obj) {
            $this->template->assign_block_vars('providers', [
                'ID'   => $name,
                'NAME' => $obj->get_display_name(),
            ]);
        }

        // Se voltar com status (redirected back)
        $status = $this->request->variable('status', '');
        if ($status) {
            $this->template->assign_var('SANDBOX_SUCCESS', 'Você retornou do gateway com o status: ' . htmlspecialchars($status));
        }

        return $this->helper->render('sandbox_body.html', 'Sandbox de Pagamentos');
    }
}
