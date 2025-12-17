<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TDeletedRows extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeletedRows', $tablePrefix);

        $this->setColumns([
            'TableId' => new Database\FInt(true, false), 
            'RowId' => new Database\FLong(true), 
            '_Modified_DateTime' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'TableId', 'RowId' ]);
    }
}
