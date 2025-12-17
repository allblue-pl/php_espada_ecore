<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TSettings extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Config_Settings', $tablePrefix);

        $this->setColumns([
            'Name' => new Database\FString(true, 32), 
            'Value' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Name' ]);
    }
}
