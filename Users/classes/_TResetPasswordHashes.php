<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TResetPasswordHashes extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Users_ResetPasswordHashes', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(true), 
            'DateTime' => new Database\FDateTime(true), 
            'Hash' => new Database\FString(true, 128), 
        ]);
        $this->setPKs([ 'Id' ]);
    }
}
