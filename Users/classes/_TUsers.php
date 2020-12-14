<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TUsers extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, 'Users_Users', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true),
            'Type' => new Database\FVarchar(true, 16),
            'LoginHash' => new Database\FVarchar(true, 256),
            'EmailHash' => new Database\FVarchar(true, 256),
            'PasswordHash' => new Database\FVarchar(true, 256),
            'Groups' => new Database\FVarchar(true, 128),
            'Active' => new Database\FBool(true),
        ]);
    }

}
