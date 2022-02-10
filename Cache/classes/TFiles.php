<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class TFiles extends _TFiles
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'f');

        $this->setJoin(
            ' LEFT JOIN Users_Users AS u_u' .
            ' ON u_u.Id = f.User_Id'
        );
    }

}
