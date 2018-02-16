<?php namespace EC\SPK;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LSPK extends E\Layout
{

    public function __construct($elem_id)
    {
        parent::__construct('SPK:module', [ 'elemId' => $elem_id ]);
    }

}
