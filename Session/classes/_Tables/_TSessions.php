<?php namespace EC\Session\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RSession_Sessions array{
 *     Id: string,
 *     Access: int|null,
 *     Data: string|null,
 * }
 */
class _TSessions extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Session_Sessions', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FString(true, 32), 
            'Access' => new Database\FInt(false, true), 
            'Data' => new Database\FText(false, 'regular'), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RSession_Sessions
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
     * @return array<_T_RSession_Sessions>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
