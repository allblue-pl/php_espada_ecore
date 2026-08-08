<?php namespace EC\ABData\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RABData_Devices array{
 *     Id: int,
 *     ItemIds_Last: int,
 *     SystemItemIds_Last: int,
 *     Hash: string,
 *     Expires: float|null,
 *     LastSync: float|null,
 *     DBSync: float|null,
 * }
 */
class _TDevices extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_Devices', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, false), 
            'ItemIds_Last' => new Database\FInt(true, false), 
            'SystemItemIds_Last' => new Database\FInt(true, false), 
            'Hash' => new Database\FString(true, 64), 
            'Expires' => new Database\FTime(false), 
            'LastSync' => new Database\FTime(false), 
            'DBSync' => new Database\FTime(false), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RABData_Devices
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
     * @return array<_T_RABData_Devices>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
