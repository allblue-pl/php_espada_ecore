<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDeviceRows extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, '_ABData_DeviceRows', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FLong(true),
            'TableId' => new Database\FInt(true),
            'RowId' => new Database\FLong(true),
        ]);
        $this->setPKs([ 'DeviceId', 'TableId', 'RowId' ]);
    }

}
