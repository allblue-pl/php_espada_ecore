<?php namespace EC\Tasks\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RTasks_Tasks array{
 *     Hash: string,
 *     User_Id: float|null,
 *     DateTime: float,
 *     Finished: bool,
 *     Info: string,
 *     Data: string,
 * }
 */
class _TTasks extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Tasks_Tasks', $tablePrefix);

        $this->setColumns([
            'Hash' => new Database\FString(true, 128), 
            'User_Id' => new Database\FLong(false), 
            'DateTime' => new Database\FDateTime(true), 
            'Finished' => new Database\FBool(true), 
            'Info' => new Database\FText(true, 'medium'), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Hash' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RTasks_Tasks
     */
    public function assertRow(array $row, bool $stripRow = false): array {
        if ($stripRow)
            $row = $this->stripRow($row);

        /* @phpstan-ignore return.type */
        return $row;
    }

    /**
     *
     * @param array $rows
     * @return array<_T_RTasks_Tasks>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
