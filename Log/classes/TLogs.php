<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Log\_Tables\_TLogs;

class TLogs extends _TLogs {
    public function __construct(MDatabase $db) {
        parent::__construct($db, 'l');

        $this->setColumnParser('Data', [
            'out' => function($row, $name, $value) {
                $value = json_decode($value, true);
                if ($value === null)
                    throw new \Exception('Cannot decode json.');

                return [
                    $name => $value['data']
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode([ 'data' => $value ]);
            }
        ]);
    }

}
