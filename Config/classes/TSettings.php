<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Config\_Tables\_TSettings;

class TSettings extends _TSettings {
    public function __construct(MDatabase $db) {
        parent::__construct($db, 's');

        /* Column Parsers */
        $this->setColumnParser('Value', [
            'out' => function($row, $name, $value) {
                return [
                    $name => json_decode($value, true)['value'],
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode((object)[ 'value' => $value ]);
            }
        ]);
    }
}
