<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDeletedRows extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix) {
        parent::__construct($db, '_ABData_DeletedRows', $tablePrefix);

        $this->setColumns([
            'TableId' => new Database\FInt(true, 64),
            'RowId' => new Database\FLong(true),
            '_Modified_DateTime' => new Database\FLong(true),
            // '_Modified_DeviceId' => new Database\FLong(true),
        ]);
        $this->setPKs([ 'TableId', 'RowId' ]);
    }

}
