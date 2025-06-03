<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TUsers extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Users_Users', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FId(true),
            'Type' => new Database\FString(true, 16),
            'LoginHash' => new Database\FString(true, 256),
            'EmailHash' => new Database\FString(true, 256),
            'PasswordHash' => new Database\FString(true, 256),
            'Groups' => new Database\FString(true, 128),
            'Active' => new Database\FBool(true),
        ]);
        $this->setPKs([ 'Id' ]);
    }

}
