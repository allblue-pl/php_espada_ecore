<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LScript extends E\Layout
{

    public function __construct($script)
    {
        parent::__construct('Basic:raw', [
            'raw' => "<script>{$script}</script>"
        ]);
    }

}
