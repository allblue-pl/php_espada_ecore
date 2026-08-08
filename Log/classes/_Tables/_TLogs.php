<?php namespace EC\Log\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RLog_Logs array{
 *     Id: int|null,
 *     User_Id: float|null,
 *     DateTime: float|null,
 *     Message: string|null,
 *     Data: string|null,
 * }
 */
class _TLogs extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Log_Logs', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(false), 
            'DateTime' => new Database\FLong(false), 
            'Message' => new Database\FString(false, 256), 
            'Data' => new Database\FText(false, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RLog_Logs
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
     * @return array<_T_RLog_Logs>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
