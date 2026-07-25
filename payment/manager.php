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

    public function __construct(\phpbb\config\config $config, \phpbb\language\language $language)
    {
        $this->config = $config;
        $this->language = $language;
        
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
     */
    public function create_payment($provider_name, array $payment_data)
    {
        $provider = $this->get_provider($provider_name);
        return $provider->create_payment($payment_data);
    }
}
