<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment;

/**
 * Manager class to orchestrate payment providers.
 */
class manager
{
    /**
     * @var array
     */
    protected $providers = [];

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\language\language */
    protected $language;

    /** @var \phpbb\log\log_interface */
    protected $log;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\db\driver\driver_interface */
    protected $db;

    public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\log\log_interface $log, \phpbb\user $user, \phpbb\db\driver\driver_interface $db)
    {
        $this->config = $config;
        $this->language = $language;
        $this->log = $log;
        $this->user = $user;
        $this->db = $db;
        
        // Carrega as bibliotecas externas (Stripe, Mercado Pago, PayPal) via Composer
        $autoload_path = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoload_path)) {
            require_once $autoload_path;
        }
    }

    /**
     * Register a payment provider. Usually called via dependency injection in services.yml.
     *
     * @param \eunaumtenhoid\payments\payment\provider_interface $provider
     */
    public function register_provider(provider_interface $provider)
    {
        $this->providers[$provider->get_name()] = $provider;
    }

    /**
     * Get all registered providers, or only the enabled ones.
     *
     * @param bool $only_enabled If true, filters out disabled providers.
     * @return array
     */
    public function get_providers($only_enabled = true)
    {
        if (!$only_enabled) {
            return $this->providers;
        }

        $active = [];
        foreach ($this->providers as $name => $provider) {
            $config_key = 'eunaumtenhoid_payments_' . $name . '_enabled';
            if (!empty($this->config[$config_key])) {
                $active[$name] = $provider;
            }
        }
        return $active;
    }

    /**
     * Get a specific provider by its machine name.
     *
     * @param string $provider_name
     * @return \eunaumtenhoid\payments\payment\provider_interface
     * @throws \Exception if the provider is not found
     */
    public function get_provider($provider_name)
    {
        if (!isset($this->providers[$provider_name])) {
            throw new \Exception(sprintf($this->language->lang('ACP_EUNAUMTENHOID_PAYMENTS_PROVIDER_NOT_FOUND'), $provider_name));
        }

        return $this->providers[$provider_name];
    }

    /**
     * Helper to create a payment directly through the manager.
     *
     * @param string $provider_name
     * @param array $payment_data
     * @return array
     * @throws \Exception
     */
    public function create_payment($provider_name, array $payment_data)
    {
        try {
            $provider = $this->get_provider($provider_name);
            return $provider->create_payment($payment_data);
        } catch (\Exception $e) {
            $user_id = $this->user->data['user_id'] ?? ANONYMOUS;
            $user_ip = $this->user->ip ?? '127.0.0.1';
            $this->log->add('critical', $user_id, $user_ip, 'LOG_PAYMENTS_ERROR', time(), [strtoupper($provider_name), $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Helper to verify a payment directly through the manager.
     *
     * @param string $provider_name
     * @param string $transaction_id
     * @return array
     * @throws \Exception
     */
    public function verify_payment($provider_name, $transaction_id)
    {
        try {
            $provider = $this->get_provider($provider_name);
            return $provider->verify_payment($transaction_id);
        } catch (\Exception $e) {
            $user_id = $this->user->data['user_id'] ?? ANONYMOUS;
            $user_ip = $this->user->ip ?? '127.0.0.1';
            $this->log->add('critical', $user_id, $user_ip, 'LOG_PAYMENTS_ERROR', time(), [strtoupper($provider_name), $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cria um novo Intent (Fatura) para a tela centralizada de pagamento.
     *
     * @param array $data Array com 'amount', 'currency', 'description', 'return_url', 'allowed_gateways'
     * @return string O intent_id gerado
     */
    public function create_intent(array $data)
    {
        global $table_prefix;
        $table = $table_prefix . 'eunaumtenhoid_payments_intents';

        $intent_id = bin2hex(random_bytes(16)); // Gera ID único
        
        $sql_ary = [
            'intent_id'        => $intent_id,
            'amount'           => (int) $data['amount'],
            'currency'         => $data['currency'] ?? 'BRL',
            'description'      => $data['description'] ?? '',
            'return_url'       => $data['return_url'] ?? '',
            'return_text'      => $data['return_text'] ?? '',
            'step1_title'      => $data['step1_title'] ?? '',
            'reward_info'      => $data['reward_info'] ?? '',
            'success_msg'      => $data['success_msg'] ?? '',
            'cancel_msg'       => $data['cancel_msg'] ?? '',
            'allowed_gateways' => isset($data['allowed_gateways']) ? json_encode($data['allowed_gateways']) : '',
            'created_at'       => time(),
        ];

        $sql = 'INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
        $this->db->sql_query($sql);

        return $intent_id;
    }

    /**
     * Recupera um Intent do banco de dados.
     *
     * @param string $intent_id
     * @return array|false
     */
    public function get_intent($intent_id)
    {
        global $table_prefix;
        $table = $table_prefix . 'eunaumtenhoid_payments_intents';

        $sql = 'SELECT * FROM ' . $table . " WHERE intent_id = '" . $this->db->sql_escape($intent_id) . "'";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($row) {
            $row['allowed_gateways'] = $row['allowed_gateways'] ? json_decode($row['allowed_gateways'], true) : [];
        }

        return $row;
    }
}
