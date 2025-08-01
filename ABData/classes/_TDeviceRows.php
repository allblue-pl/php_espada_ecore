<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDeviceRows extends Database\TTable {
    public function __construct(EC\MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeviceRows', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FInt(true, false), 
            'TableId' => new Database\FInt(true, false), 
            'RowId' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'DeviceId', 'TableId', 'RowId' ]);
    }
}
