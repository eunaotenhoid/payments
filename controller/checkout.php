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
use Symfony\Component\HttpFoundation\JsonResponse;

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

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\config\config */
    protected $config;

    public function __construct(
        \phpbb\template\template $template,
        \eunaumtenhoid\payments\payment\manager $payment_manager,
        \phpbb\request\request $request,
        \phpbb\controller\helper $helper,
        \phpbb\event\dispatcher_interface $dispatcher = null,
        \phpbb\language\language $language = null,
        \phpbb\user $user = null,
        \phpbb\config\config $config = null
    ) {
        $this->template = $template;
        $this->payment_manager = $payment_manager;
        $this->request = $request;
        $this->helper = $helper;
        $this->dispatcher = $dispatcher;
        $this->language = $language;
        $this->user = $user;
        $this->config = $config;

        if ($this->language) {
            $this->language->add_lang('info_acp_main', 'eunaumtenhoid/payments');
        } elseif ($this->user) {
            $this->user->add_lang_ext('eunaumtenhoid/payments', 'info_acp_main');
        }
    }

    public function handle($intent_id)
    {
        $intent = $this->payment_manager->get_intent($intent_id);

        if (!$intent) {
            trigger_error('PAYMENTS_ERROR_NOT_FOUND');
        }

        // Se o usuário postou o formulário escolhendo o gateway (POST padrão ou AJAX)
        $is_submit = $this->request->is_set_post('submit') || ($this->request->is_set_post('gateway') && $this->request->is_ajax());

        if ($is_submit) {
            if (!check_form_key('checkout_form')) {
                if ($this->request->is_ajax()) {
                    return new JsonResponse(['success' => false, 'error' => $this->language->lang('FORM_INVALID')]);
                }
                trigger_error('FORM_INVALID');
            }

            $provider_name = $this->request->variable('gateway', '');

            // Validar se o gateway escolhido é permitido para esta fatura
            if (!empty($intent['allowed_gateways']) && !in_array($provider_name, $intent['allowed_gateways'])) {
                if ($this->request->is_ajax()) {
                    return new JsonResponse(['success' => false, 'error' => $this->language->lang('PAYMENTS_ERROR_NOT_ALLOWED')]);
                }
                trigger_error('PAYMENTS_ERROR_NOT_ALLOWED');
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
                    // Grava a transação para auditoria e verificação real com o gateway
                    $tx_id = $data['transaction_id'] ?? '';
                    $this->payment_manager->record_transaction(
                        $intent_id,
                        $provider_name,
                        $tx_id,
                        $intent['amount'],
                        $intent['currency'],
                        $data['status'] ?? 'pending'
                    );

                    $use_modal = ($provider_name === 'coinpayments');
                    // A rota de retorno para o modal chama a tela oficial de conclusão que fará a checagem real
                    $return_bridge = $bridge_return_url;

                    if ($this->request->is_ajax()) {
                        return new JsonResponse([
                            'success'    => true,
                            'url'        => $data['url'],
                            'modal'      => $use_modal,
                            'return_url' => $return_bridge,
                        ]);
                    }

                    if ($use_modal) {
                        $this->template->assign_vars([
                            'S_SHOW_MODAL'       => true,
                            'MODAL_CHECKOUT_URL' => $data['url'],
                            'MODAL_RETURN_URL'   => $return_bridge,
                        ]);
                    } else {
                        return new RedirectResponse($data['url']);
                    }
                } else {
                    $no_url_msg = ($this->language && $this->language->is_set('PAYMENTS_ERROR_NO_URL')) ? $this->language->lang('PAYMENTS_ERROR_NO_URL') : 'A API não retornou uma URL de checkout.';
                    if ($this->request->is_ajax()) {
                        return new JsonResponse(['success' => false, 'error' => $no_url_msg]);
                    }
                    $this->template->assign_var('CHECKOUT_ERROR', $no_url_msg);
                }
            } catch (\Exception $e) {
                if ($this->request->is_ajax()) {
                    return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
                }
                $this->template->assign_var('CHECKOUT_ERROR', $e->getMessage());
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
            $step1_label = ($this->language && $this->language->is_set('PAYMENTS_STEP_REVISION')) ? $this->language->lang('PAYMENTS_STEP_REVISION') : 'Revisão';
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

        $breadcrumb_finish = ($this->language && $this->language->is_set('PAYMENTS_BREADCRUMB_FINISH')) ? $this->language->lang('PAYMENTS_BREADCRUMB_FINISH') : 'Finalizar Pagamento';

        // Breadcrumbs do phpBB (Navlinks)
        $this->template->assign_block_vars('navlinks', [
            'BREADCRUMB_NAME' => $breadcrumb_finish,
            'U_BREADCRUMB'    => $this->helper->route('eunaumtenhoid_payments_checkout', ['intent_id' => $intent_id]),
        ]);

        add_form_key('checkout_form');

        return $this->helper->render('checkout_body.html', $breadcrumb_finish);
    }

    /**
     * Tela Oficial de Conclusão / Sucesso (Passo 3)
     */
    public function completed($intent_id)
    {
        $intent = $this->payment_manager->get_intent($intent_id);

        if (!$intent) {
            trigger_error('PAYMENTS_ERROR_NOT_FOUND');
        }

        // Busca se há transação registrada para este intent
        $tx = $this->payment_manager->get_transaction_by_intent($intent_id);
        $status = 'pending';

        if ($tx && !empty($tx['transaction_id'])) {
            try {
                // Consulta a API do provedor em tempo real (ex: CoinPayments API v2)
                $verify = $this->payment_manager->verify_payment($tx['provider'], $tx['transaction_id']);
                if (!empty($verify['status'])) {
                    $status = $verify['status'];
                    $this->payment_manager->update_transaction_status($tx['id'], $status);
                }
            } catch (\Exception $e) {
                // Em caso de erro temporário de rede, mantém o status gravado
                $status = $tx['status'] ?? 'pending';
            }
        } else {
            // Se não há transação com verificação, usa o parâmetro se informado (padrão pending)
            $status = $this->request->variable('status', 'pending');
        }

        $is_success = in_array($status, ['success', 'approved', 'paid'], true);
        $is_pending = in_array($status, ['pending', 'unpaid', 'scheduled', 'draft'], true);

        // Monta a URL de retorno final para a extensão de origem repassando status REAL verificado
        $return_url = $intent['return_url'];
        if (!empty($return_url)) {
            // Se a reescrita de URL (mod_rewrite) estiver desativada no phpBB, assegura que rotas internas usem /app.php/
            $is_rewrite = ($this->config && !empty($this->config['enable_mod_rewrite']));
            if (!$is_rewrite) {
                $board_url = generate_board_url();
                if (strpos($return_url, $board_url) === 0 && strpos($return_url, '/app.php') === false) {
                    $rel_path = substr($return_url, strlen($board_url));
                    // Se não for um script PHP físico direto (ex: index.php, viewtopic.php)
                    if (!preg_match('#^/[a-zA-Z0-9_\-]+\.php#i', $rel_path)) {
                        $return_url = $board_url . '/app.php' . $rel_path;
                    }
                }
            }

            $separator = (strpos($return_url, '?') !== false) ? '&' : '?';
            $final_status = $is_success ? 'success' : ($is_pending ? 'pending' : 'failed');
            $url_params = [
                'status'     => $final_status,
                'gateway'    => $tx['provider'] ?? '',
                'tx_id'      => $tx['transaction_id'] ?? '',
                'payment_id' => $tx['transaction_id'] ?? '',
            ];
            $final_url = $return_url . $separator . http_build_query($url_params);
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
            $vars = ['intent', 'is_success', 'is_pending', 'status', 'final_url', 'return_label', 'custom_msg'];
            extract($this->dispatcher->trigger_event('eunaumtenhoid.payments.checkout_completed', compact($vars)));
        }

        $raw_step1 = $intent['step1_title'] ?? '';
        if (!empty($raw_step1)) {
            $step1_label = ($this->language && $this->language->is_set($raw_step1)) ? $this->language->lang($raw_step1) : $raw_step1;
        } else {
            $step1_label = ($this->language && $this->language->is_set('PAYMENTS_STEP_REVISION')) ? $this->language->lang('PAYMENTS_STEP_REVISION') : 'Revisão';
        }

        $this->template->assign_vars([
            'INTENT_AMOUNT'      => number_format($intent['amount'] / 100, 2, ',', '.'),
            'INTENT_CURRENCY'    => $intent['currency'],
            'INTENT_DESCRIPTION' => $intent['description'],
            'IS_SUCCESS'         => $is_success,
            'IS_PENDING'         => $is_pending,
            'PAYMENT_STATUS'     => $status,
            'CUSTOM_MSG'         => $custom_msg,
            'STEP1_LABEL'        => $step1_label,
            'U_RETURN_URL'       => $final_url,
            'RETURN_LABEL'       => $return_label,
        ]);

        if ($is_success) {
            $breadcrumb_title = ($this->language && $this->language->is_set('PAYMENTS_BREADCRUMB_COMPLETED')) ? $this->language->lang('PAYMENTS_BREADCRUMB_COMPLETED') : 'Pagamento Concluído';
        } elseif ($is_pending) {
            $breadcrumb_title = ($this->language && $this->language->is_set('PAYMENTS_PENDING_TITLE')) ? $this->language->lang('PAYMENTS_PENDING_TITLE') : 'Pagamento Pendente';
        } else {
            $breadcrumb_title = ($this->language && $this->language->is_set('PAYMENTS_BREADCRUMB_FAILED')) ? $this->language->lang('PAYMENTS_BREADCRUMB_FAILED') : 'Falha no Pagamento';
        }

        // Breadcrumbs do phpBB (Navlinks)
        $this->template->assign_block_vars('navlinks', [
            'BREADCRUMB_NAME' => $breadcrumb_title,
            'U_BREADCRUMB'    => $this->helper->route('eunaumtenhoid_payments_completed', ['intent_id' => $intent_id]),
        ]);

        return $this->helper->render('checkout_completed.html', $breadcrumb_title);
    }
}
