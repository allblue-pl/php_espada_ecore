<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TTasks extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix = 't')
    {
        parent::__construct($db, 'Tasks_Tasks', $tablePrefix);

        $this->setColumns([
            'Hash' => new Database\FString(true, 128),
            'User_Id' => new Database\FInt(false),
            'DateTime' => new Database\FDateTime(true),
            'Finished' => new Database\FBool(true),
            'Info' => new Database\FText(true, 'medium'),
            'Data' => new Database\FText(true, 'medium'),
        ]);
        $this->setPKs([ 'Hash' ]);
    }

}
