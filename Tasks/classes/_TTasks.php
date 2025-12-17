<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TTasks extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Tasks_Tasks', $tablePrefix);

        $this->setColumns([
            'Hash' => new Database\FString(true, 128), 
            'User_Id' => new Database\FLong(false), 
            'DateTime' => new Database\FDateTime(true), 
            'Finished' => new Database\FBool(true), 
            'Info' => new Database\FText(true, 'medium'), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Hash' ]);
    }
}
