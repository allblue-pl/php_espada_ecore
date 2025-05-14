<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TResetPasswordHashes extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, 'Users_ResetPasswordHashes', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true),
            'User_Id' => new Database\FLong(true),
            'DateTime' => new Database\FDateTime(true),
            'Hash' => new Database\FString(true, 128),
        ]);
    }

}
