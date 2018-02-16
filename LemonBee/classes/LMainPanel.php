<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LMainPanel extends EC\LSPK
{

    private $panels = null;

    public function __construct(SLemonBee $site, $title = '')
    {
        parent::__construct('eLemonBee_MainPanel');

        $site->m->spk->addScript('LemonBee:MainPanel');
        $site->m->spk->addModule('eLemonBee_MainPanel');
        $site->m->spk->addFields('eLemonBee_MainPanel', [
            'panels' => $site->lbGetPanels()
        ]);
    }

}
