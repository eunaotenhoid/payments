<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\migrations;

class install extends \phpbb\db\migration\migration
{
    /**
     * Define the schema for the extension
     */
    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'payments_transactions' => [
                    'COLUMNS' => [
                        'id'               => ['UINT', null, 'auto_increment'],
                        'transaction_id'   => ['VCHAR:255', ''], // ID returned by Stripe/MP
                        'provider'         => ['VCHAR:50', ''], // 'stripe', 'mercadopago'
                        'amount'           => ['BINT', 0], // Integer amount in cents
                        'currency'         => ['VCHAR:3', 'BRL'],
                        'status'           => ['VCHAR:20', 'pending'], // pending, paid, failed, cancelled
                        'user_id'          => ['UINT', 0],
                        'extension_caller' => ['VCHAR:255', ''], // Which extension requested this
                        'created_at'       => ['TIMESTAMP', 0],
                        'updated_at'       => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'tx_id' => ['INDEX', 'transaction_id'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Revert the schema changes
     */
    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'payments_transactions',
            ],
        ];
    }
}
