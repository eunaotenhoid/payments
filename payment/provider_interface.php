<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\payment;

/**
 * Interface that all payment providers must implement.
 */
interface provider_interface
{
    /**
     * Get the unique machine-readable name of the provider (e.g. 'stripe', 'mercadopago').
     *
     * @return string
     */
    public function get_name();

    /**
     * Get the human-readable display name (e.g. 'Stripe', 'Mercado Pago').
     *
     * @return string
     */
    public function get_display_name();

    /**
     * Create a new payment/checkout session.
     * 
     * @param array $payment_data Contains amount, currency, description, etc.
     * @return array Returns an array with 'status', 'url' (for redirect) or 'qr_code' (for PIX), etc.
     */
    public function create_payment(array $payment_data);

    /**
     * Verify the status of an existing payment (useful for webhooks or manual checks).
     *
     * @param string $transaction_id The ID of the transaction at the provider.
     * @return array Returns an array with updated status information.
     */
    public function verify_payment($transaction_id);
}
