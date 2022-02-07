<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TSettings extends _TSettings
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'Config_Settings', 's');

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
