<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TInfos extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'App_Infos', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(true), 
            'AuthenticationHash' => new Database\FString(true, 256), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);
    }
}
