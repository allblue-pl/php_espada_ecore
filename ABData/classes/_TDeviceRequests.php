<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TDeviceRequests extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeviceRequests', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FInt(true, false), 
            'RequestId' => new Database\FInt(true, false), 
        ]);
        $this->setPKs([ 'DeviceId', 'RequestId' ]);
    }
}
