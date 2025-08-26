<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDeviceRequests extends Database\TTable {
    public function __construct(EC\MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeviceRequests', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FInt(true, false), 
            'RequestId' => new Database\FInt(true, false), 
        ]);
        $this->setPKs([ 'DeviceId', 'RequestId' ]);
    }
}
