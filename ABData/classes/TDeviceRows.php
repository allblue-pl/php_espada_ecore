<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E;
use EC\Database\MDatabase;

class TDeviceRows extends _TDeviceRows {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 'abd_drw');
    }

}
