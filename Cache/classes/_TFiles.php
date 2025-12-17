<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TFiles extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Cache_Files', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(false), 
            'Hash' => new Database\FString(true, 128), 
            'Expires' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'Id' ]);
    }
}
