<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDevices extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix) {
        parent::__construct($db, '_ABData_Devices', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true),
            'ItemIds_Last' => new Database\FInt(true),
            'SystemItemIds_Last' => new Database\FInt(true),
            'Hash' => new Database\FString(true, 64),
            'Expires' => new Database\FTime(false),
            'LastSync' => new Database\FTime(false),
            'DBSync' => new Database\FTime(false),
        ]);
        $this->setPKs([ 'Id' ]);
    }

}
