<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LHtml extends E\Layout
{

    public function __construct($html)
    {
        parent::__construct('Basic:raw', E\Fields::_([
            'raw' => $html
        ]));
    }

}
