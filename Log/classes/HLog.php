<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class HLog extends Database\TTable
{

    static public function Add(EC\MDatabase $db, $user_id, string $message, $data = null)
    {
        (new TLogs($db))->update([[
            'Id' => null,
            'User_Id' => $user_id,

            'DateTime' => EC\HDate::GetTime(),
            'Message' => $message,
            'Data' => $data,
        ]]);
    }

    static public function Add_Array(EC\MDatabase $db, array $logs)
    {
        (new TLogs($db))->update($logs);
    }

}
