<?php
/**
 *
 * @copyright (c) 2026 eunaumtenhoid
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eunaumtenhoid\payments\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    /** @var \phpbb\template\template */
    protected $template;

    public function __construct(\phpbb\template\template $template)
    {
        $this->template = $template;
    }

    /**
     * Define quais eventos do phpBB queremos "escutar"
     */
    static public function getSubscribedEvents()
    {
        return [
            // Exemplo: 'core.page_header' => 'add_page_header_scripts',
        ];
    }

    /**
     * Exemplo de função que injetaria o JS do Stripe/MercadoPago no fórum
     * se fôssemos usar Checkout Transparente.
     */
    /*
    public function add_page_header_scripts($event)
    {
        // $this->template->assign_var('S_PAYMENTS_LOADED', true);
    }
    */
}
