<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TDeviceRows extends _TDeviceRows {

    public function __construct(EC\MDatabase $db) {
        parent::__construct($db, 'abd_drw');
    }

}
