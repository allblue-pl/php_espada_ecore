<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDeviceRequests extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix) {
        parent::__construct($db, '_ABData_DeviceRequests', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FInt(true),
            'RequestId' => new Database\FInt(true),
        ]);
        $this->setPKs([ 'DeviceId', 'RequestId' ]);
    }

}
