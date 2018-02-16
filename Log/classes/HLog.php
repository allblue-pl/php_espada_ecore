<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class HLog extends Database\TTable
{

    static public function Add(EC\MDatabase $db, $user_id, $message, $data = null)
    {
        (new TLogs($db))->update([[
            'Id' => null,
            'User_Id' => $user_id,

            'DateTime' => time(),
            'Message' => $message,
            'Data' => $data,
        ]]);
    }

}
