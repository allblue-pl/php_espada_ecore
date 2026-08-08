<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Session\_Tables\_TSessions;

class TSessions extends _TSessions {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 's_s');
    }

}
