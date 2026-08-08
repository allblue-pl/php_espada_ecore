<?php namespace EC\ABData\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RABData_DeletedRows array{
 *     TableId: int,
 *     RowId: float,
 *     _Modified_DateTime: float,
 * }
 */
class _TDeletedRows extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'ABData_DeletedRows', $tablePrefix);

        $this->setColumns([
            'TableId' => new Database\FInt(true, false), 
            'RowId' => new Database\FLong(true), 
            '_Modified_DateTime' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'TableId', 'RowId' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RABData_DeletedRows
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
     * @return array<_T_RABData_DeletedRows>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
