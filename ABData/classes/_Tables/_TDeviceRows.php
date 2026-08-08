<?php namespace EC\ABData\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RABData_DeviceRows array{
 *     DeviceId: int,
 *     TableId: int,
 *     RowId: float,
 * }
 */
class _TDeviceRows extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeviceRows', $tablePrefix);

        $this->setColumns([
            'DeviceId' => new Database\FInt(true, false), 
            'TableId' => new Database\FInt(true, false), 
            'RowId' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'DeviceId', 'TableId', 'RowId' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RABData_DeviceRows
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
     * @return array<_T_RABData_DeviceRows>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
