<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TRecoverPasswordHashes extends _TRecoverPasswordHashes
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'u_rph');
    }

}
