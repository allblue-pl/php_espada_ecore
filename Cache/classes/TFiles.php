<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class TFiles extends EC\Database\TTable
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'Cache_Files', 'f');

        $this->setColumns([
            'Id'        => new EC\Database\FInt(true, 11),
            'User_Id'   => new EC\Database\FInt(true, 11),
            'Hash'      => new EC\Database\FVarchar(true, 128),
            'Expires'   => new EC\Database\FDateTime(true)
        ]);

        $this->setJoin(
            'LEFT JOIN Users_Users AS u_u' .
            ' ON u_u.Id = f.User_Id'
        );
    }

}
