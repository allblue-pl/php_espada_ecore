<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TSessions extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Session_Sessions', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FString(true, 32), 
            'Access' => new Database\FInt(false, true), 
            'Data' => new Database\FText(false, 'regular'), 
        ]);
        $this->setPKs([ 'Id' ]);
    }
}
