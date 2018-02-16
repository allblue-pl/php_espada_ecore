<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VBool extends Forms\VField
{

    public function __construct()
    {
        parent::__construct([], []);
    }

    protected function _validate(&$value)
    {

    }

}
