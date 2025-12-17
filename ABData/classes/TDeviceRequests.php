<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E;
use EC\Database\MDatabase;

class TDeviceRequests extends _TDeviceRequests {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 'abd_drq');
    }

}
