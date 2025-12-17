<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TDeviceRows extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeviceRows', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FInt(true, false), 
            'TableId' => new Database\FInt(true, false), 
            'RowId' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'DeviceId', 'TableId', 'RowId' ]);
    }
}
