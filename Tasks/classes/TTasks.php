<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;

class TTasks extends _TTasks {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 'r');

        $this->setColumnParser('Info', [
            'out' => function($row, $name, $value) {
                return [
                    $name => json_decode($value, true)['info']
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode([ 'info' => $value ]);
            }
        ]);
        $this->setColumnParser('Data', [
            'out' => function($row, $name, $value) {
                return [
                    $name => json_decode($value, true)['data']
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode([ 'data' => $value ]);
            }
        ]);
    }

}
