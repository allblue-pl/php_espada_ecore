<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Database\TTable;
use EC\Date\HDate;

class HLog {
    static public function Add(MDatabase $db, $user_id, string $message, $data = null) {
        (new TLogs($db))->update([[
            'Id' => null,
            'User_Id' => $user_id,

            'DateTime' => HDate::GetTime(),
            'Message' => $message,
            'Data' => $data,
        ]]);
    }

    static public function Add_Array(MDatabase $db, array $logs) {
        (new TLogs($db))->update($logs);
    }
}
