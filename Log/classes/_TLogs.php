<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TLogs extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Log_Logs', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(false), 
            'DateTime' => new Database\FLong(false), 
            'Message' => new Database\FString(false, 256), 
            'Data' => new Database\FText(false, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);
    }
}
