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
    /**
     *
     * @param array $row
     * @return _T_RSession_Sessions
     */
    static public function AssertRow(array $row): array {
        /* @phpstan-ignore return.type */
        return $row;
    }

    /**
     *
     * @param array $rows
     * @return array<_T_RSession_Sessions>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }


    public function __construct(MDatabase $db, $tablePrefix = 's_s') {
        parent::__construct($db, 'Session_Sessions', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FString(true, 32), 
            'Access' => new Database\FInt(false, true), 
            'Data' => new Database\FText(false, 'regular'), 
        ]);
        $this->setPKs([ 'Id' ]);

    }
}
