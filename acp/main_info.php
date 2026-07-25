<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\acp;

class main_info
{
    public function module()
    {
        return [
            'filename' => '\eunaumtenhoid\payments\acp\main_module',
            'title'    => 'ACP_EUNAUMTENHOID_PAYMENTS_TITLE',
            'modes'    => [
                'settings' => [
                    'title' => 'ACP_EUNAUMTENHOID_PAYMENTS_SETTINGS',
                    'auth'  => 'ext_eunaumtenhoid/payments && acl_a_board',
                    'cat'   => ['ACP_EUNAUMTENHOID_PAYMENTS_TITLE'],
                ],
            ],
        ];
    }
}
