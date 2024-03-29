<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TInfos extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix = 't')
    {
        parent::__construct($db, 'App_Infos', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true),
            'User_Id' => new Database\FLong(true),
            'AuthenticationHash' => new Database\FString(true, 256),
            'Data' => new Database\FText(true, 'medium'),
        ]);
        $this->setPKs([ 'Id' ]);
    }

}
