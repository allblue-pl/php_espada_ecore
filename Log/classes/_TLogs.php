<?php namespace EC\Log;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TLogs extends Database\TTable {

    public function __construct(EC\MDatabase $db, $tablePrefix = 't')
    {
        parent::__construct($db, 'Log_Logs', $tablePrefix);

        $this->setColumns([
            'Id'                => new Database\FId(false, 11),
            'User_Id'           => new Database\FLong(false, 11),

            'DateTime'          => new Database\FLong(false),
            'Message'           => new Database\FString(true, 256),
            'Data'              => new Database\FText(true, 'medium')
        ]);
        $this->setPKs([ 'Id' ]);
    }

}
