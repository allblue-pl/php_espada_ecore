<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TDevices extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_Devices', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, false), 
            'ItemIds_Last' => new Database\FInt(true, false), 
            'SystemItemIds_Last' => new Database\FInt(true, false), 
            'Hash' => new Database\FString(true, 64), 
            'Expires' => new Database\FTime(false), 
            'LastSync' => new Database\FTime(false), 
            'DBSync' => new Database\FTime(false), 
        ]);
        $this->setPKs([ 'Id' ]);
    }
}
