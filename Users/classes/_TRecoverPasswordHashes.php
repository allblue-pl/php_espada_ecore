<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TRecoverPasswordHashes extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, 'Users_RecoverPasswordHashes', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true),
            'User_Id' => new Database\FInt(true),
            'DateTime' => new Database\FTime(true),
            'Hash' => new Database\FString(true, 128),
        ]);
    }

}
