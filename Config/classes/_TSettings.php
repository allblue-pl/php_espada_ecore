<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TSettings extends Database\TTable {
    public function __construct(EC\MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Config_Settings', $tablePrefix);

        $this->setColumns([
            'Name' => new Database\FString(true, 32), 
            'Value' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Name' ]);
    }
}
