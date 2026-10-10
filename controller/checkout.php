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

class checkout
{
    /** @var \phpbb\template\template */
    protected $template;

    /** @var \eunaumtenhoid\payments\payment\manager */
    protected $payment_manager;

    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\event\dispatcher_interface */
    protected $dispatcher;

    /** @var \phpbb\language\language */
    protected $language;

    public function __construct(\phpbb\template\template $template, \eunaumtenhoid\payments\payment\manager $payment_manager, \phpbb\request\request $request, \phpbb\controller\helper $helper, \phpbb\event\dispatcher_interface $dispatcher = null, \phpbb\language\language $language = null)
    {
        $this->template = $template;
        $this->payment_manager = $payment_manager;
        $this->request = $request;
        $this->helper = $helper;
        $this->dispatcher = $dispatcher;
        $this->language = $language;
    }

    public function handle($intent_id)
    {
        $intent = $this->payment_manager->get_intent($intent_id);

        if (!$intent) {
            trigger_error('Fatura não encontrada ou já expirada.');
        }

        // Se o usuário postou o formulário escolhendo o gateway
        if ($this->request->is_set_post('submit')) {
            if (!check_form_key('checkout_form')) {
                trigger_error('FORM_INVALID');
            }

            $provider_name = $this->request->variable('gateway', '');

            // Validar se o gateway escolhido é permitido para esta fatura
            if (!empty($intent['allowed_gateways']) && !in_array($provider_name, $intent['allowed_gateways'])) {
                trigger_error('Método de pagamento não permitido para esta fatura.');
            }

            try {
                // Redireciona o gateway para nossa página de conclusão oficial, que por sua vez tem o botão dinâmico de retorno
                $bridge_return_url = $this->helper->route('eunaumtenhoid_payments_completed', ['intent_id' => $intent_id], true, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                $data = $this->payment_manager->create_payment($provider_name, [
                    'amount'      => $intent['amount'],
                    'currency'    => $intent['currency'],
                    'description' => $intent['description'],
                    'return_url'  => $bridge_return_url
                ]);

                if (isset($data['url']) && !empty($data['url'])) {
                    return new RedirectResponse($data['url']);
                } else {
                    $this->template->assign_var('CHECKOUT_ERROR', 'A API não retornou uma URL de checkout.');
                }
            } catch (\Exception $e) {
                $this->template->assign_var('CHECKOUT_ERROR', 'Erro ao processar: ' . $e->getMessage());
            }
        }

        // Exibir opções
        $providers = $this->payment_manager->get_providers(true);
        $has_providers = false;

        foreach ($providers as $name => $obj) {
            // Filtrar apenas os gateways permitidos por essa fatura (se houver restrição)
            if (!empty($intent['allowed_gateways']) && !in_array($name, $intent['allowed_gateways'])) {
                continue;
            }

            $has_providers = true;
            $this->template->assign_block_vars('gateways', [
                'ID'   => $name,
                'NAME' => $obj->get_display_name(),
            ]);
        }

        $raw_step1 = $intent['step1_title'] ?? '';
        if (!empty($raw_step1)) {
            $step1_label = ($this->language && $this->language->is_set($raw_step1)) ? $this->language->lang($raw_step1) : $raw_step1;
        } else {
            $step1_label = 'Revisão';
        }

        $this->template->assign_vars([
            'INTENT_AMOUNT'      => number_format($intent['amount'] / 100, 2, ',', '.'),
            'INTENT_CURRENCY'    => $intent['currency'],
            'INTENT_DESCRIPTION' => $intent['description'],
            'INTENT_REWARD_INFO' => $intent['reward_info'] ?? '',
            'STEP1_LABEL'        => $step1_label,
            'HAS_GATEWAYS'       => $has_providers,
            'U_ACTION'           => $this->helper->route('eunaumtenhoid_payments_checkout', ['intent_id' => $intent_id]),
        ]);

        // Breadcrumbs do phpBB (Navlinks)
        $this->template->assign_block_vars('navlinks', [
            'BREADCRUMB_NAME' => 'Finalizar Pagamento',
            'U_BREADCRUMB'    => $this->helper->route('eunaumtenhoid_payments_checkout', ['intent_id' => $intent_id]),
        ]);

        add_form_key('checkout_form');

        return $this->helper->render('checkout_body.html', 'Finalizar Pagamento');
    }

    /**
     * Tela Oficial de Conclusão / Sucesso (Passo 3)
     */
    public function completed($intent_id)
    {
        $intent = $this->payment_manager->get_intent($intent_id);

        if (!$intent) {
            trigger_error('Fatura não encontrada ou já expirada.');
        }

        $status = $this->request->variable('status', 'success');
        $is_success = in_array($status, ['success', 'approved', 'paid']);

        // Monta a URL de retorno final para a extensão de origem repassando status
        $return_url = $intent['return_url'];
        if (!empty($return_url)) {
            $separator = (strpos($return_url, '?') !== false) ? '&' : '?';
            $final_url = $return_url . $separator . 'status=' . urlencode($status);
        } else {
            $final_url = generate_board_url();
        }

        // Suporte dinâmico a chave de idioma ou texto puro
        $raw_return_text = $intent['return_text'] ?? '';
        if (!empty($raw_return_text)) {
            $translated_return_text = ($this->language && $this->language->is_set($raw_return_text)) ? $this->language->lang($raw_return_text) : $raw_return_text;
            $return_label = ($this->language && $this->language->is_set('PAYMENTS_RETURN_TO')) 
                ? sprintf($this->language->lang('PAYMENTS_RETURN_TO'), $translated_return_text) 
                : 'Concluir e Retornar a ' . $translated_return_text;
        } else {
            $return_label = ($this->language && $this->language->is_set('PAYMENTS_RETURN_DEFAULT')) 
                ? $this->language->lang('PAYMENTS_RETURN_DEFAULT') 
                : 'Concluir e Retornar';
        }

        // Mensagens customizadas (podem ser texto ou chave de idioma)
        $raw_custom_msg = $is_success ? ($intent['success_msg'] ?? '') : ($intent['cancel_msg'] ?? '');
        $custom_msg = '';
        if (!empty($raw_custom_msg)) {
            $custom_msg = ($this->language && $this->language->is_set($raw_custom_msg)) ? $this->language->lang($raw_custom_msg) : $raw_custom_msg;
        }

        // Disparo de Evento phpBB para extensões terceiras escutarem
        if ($this->dispatcher) {
            /**
             * Evento disparado ao renderizar a tela de conclusão do pagamento.
             * Permite extensões terceiras manipularem dados, títulos ou mensagens.
             */
            $vars = ['intent', 'is_success', 'status', 'final_url', 'return_label', 'custom_msg'];
            extract($this->dispatcher->trigger_event('eunaumtenhoid.payments.checkout_completed', compact($vars)));
        }

        $raw_step1 = $intent['step1_title'] ?? '';
        if (!empty($raw_step1)) {
            $step1_label = ($this->language && $this->language->is_set($raw_step1)) ? $this->language->lang($raw_step1) : $raw_step1;
        } else {
            $step1_label = 'Revisão';
        }

        $this->template->assign_vars([
            'INTENT_AMOUNT'      => number_format($intent['amount'] / 100, 2, ',', '.'),
            'INTENT_CURRENCY'    => $intent['currency'],
            'INTENT_DESCRIPTION' => $intent['description'],
            'IS_SUCCESS'         => $is_success,
            'PAYMENT_STATUS'     => $status,
            'CUSTOM_MSG'         => $custom_msg,
            'STEP1_LABEL'        => $step1_label,
            'U_RETURN_URL'       => $final_url,
            'RETURN_LABEL'       => $return_label,
        ]);

        // Breadcrumbs do phpBB (Navlinks)
        $this->template->assign_block_vars('navlinks', [
            'BREADCRUMB_NAME' => $is_success ? 'Pagamento Concluído' : 'Falha no Pagamento',
            'U_BREADCRUMB'    => $this->helper->route('eunaumtenhoid_payments_completed', ['intent_id' => $intent_id]),
        ]);

        return $this->helper->render('checkout_completed.html', $is_success ? 'Pagamento Concluído' : 'Falha no Pagamento');
    }
}
