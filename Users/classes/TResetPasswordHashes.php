<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TResetPasswordHashes extends _TResetPasswordHashes {

    public function __construct(EC\MDatabase $db) {
        parent::__construct($db, 'u_rph');
    }

}
