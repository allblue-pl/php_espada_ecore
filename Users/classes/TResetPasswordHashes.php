<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;

class TResetPasswordHashes extends _TResetPasswordHashes {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 'u_rph');
    }

}
