<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HTasks {

    static public function Create(EC\MDatabase $db, $user_id = null) {
        return new CTask(null, $user_id, false, [], []);
    }

    static public function Get(EC\MDatabase $db, $task_hash, $user_id = null) {
        $where_conditions = [
            [ 'Hash', '=', $task_hash ]
        ];
        if ($user_id !== null)
            $where_conditions[] = [ 'User_Id', '=', $user_id ];

        $row = (new TTasks($db))->row_Where($where_conditions);

        if ($row === null)
            return null;

        return new CTask($row['Hash'], $row['User_Id'], $row['Finished'],
                $row['Info'], $row['Data']);
    }

}
