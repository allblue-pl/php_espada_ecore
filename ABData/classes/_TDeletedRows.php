<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TDeletedRows extends Database\TTable {
    public function __construct(EC\MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeletedRows', $tablePrefix);

        $this->setColumns([
            'TableId' => new Database\FInt(true, false), 
            'RowId' => new Database\FLong(true), 
            '_Modified_DateTime' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'TableId', 'RowId' ]);
    }
}
