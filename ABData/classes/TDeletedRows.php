<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;

class TDeletedRows extends _TDeletedRows {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 'abd_dlr');
    }

}
