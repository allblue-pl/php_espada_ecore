<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TTasks extends Database\TTable
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'Tasks_Tasks', 'r');

        $this->setColumns([
            'Hash'      => new Database\FVarchar(true, 128),
            'User_Id'   => new Database\FInt(true, 11),

            'DateTime'  => new Database\FDateTime(true),

            'Finished'  => new Database\FBool(true),
            'Info'      => new Database\FText(true, 'medium'),
            'Data'      => new Database\FText(true, 'medium')
        ]);

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
