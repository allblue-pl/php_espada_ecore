<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TLogs extends Database\TTable
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'Log_Logs', 'l');

        $this->setColumns([
            'Id'                => new Database\FInt(true, 11),
            'User_Id'           => new Database\FInt(false, 11),

            'DateTime'          => new Database\FDateTime(false),
            'Message'           => new Database\FVarchar(true, 256),
            'Data'              => new Database\FText(true, 'medium')
        ]);

        $this->setColumnParser('Data', [
            'out' => function($row, $name, $value) {
                $value = json_decode($value);
                if ($value === null)
                    throw new \Exception('Cannot decode json.');

                return [
                    $name => $value['data']
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode([ 'data' => (object)$value ]);
            }
        ]);
    }

}
