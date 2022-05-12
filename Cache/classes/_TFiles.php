<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TFiles extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix = 't')
    {
        parent::__construct($db, 'Cache_Files', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true),
            'User_Id' => new Database\FInt(false),
            'Hash' => new Database\FString(true, 128),
            'Expires' => new Database\FTime(true),
        ]);
        $this->setPKs([ 'Id' ]);
    }

}
