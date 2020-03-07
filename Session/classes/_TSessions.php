<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TSessions extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, 'Session_Sessions', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FVarchar(true, 32),
            'Access' => new Database\FInt(false),
            'Data' => new Database\FText(false, 'regular'),
        ]);
    }

}
