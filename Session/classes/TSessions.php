<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TSessions extends _TSessions {

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 's_s');
    }

}
