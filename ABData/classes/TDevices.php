<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E;
use EC\Database\MDatabase;

class TDevices extends _TDevices {

    public function __construct(MDatabase $db) {
        parent::__construct($db, 'abd_d');

        // $this->setColumnParser('ItemIds_Used', [
        //     'out' => function($row, $name, $value) {
        //         return [
        //             $name => $value === null ? 
        //                     null : json_decode($value, true)['value']
        //         ];
        //     },
        //     'in' => function($row, $name, $value) {
        //         return json_encode([ 'value' => $value ]);
        //     }
        // ]);
    }

}
