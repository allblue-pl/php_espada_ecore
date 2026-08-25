<?php namespace EC\Users\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RUsers_ResetPasswordHashes array{
 *     Id: int|null,
 *     User_Id: float,
 *     DateTime: float,
 *     Hash: string,
 * }
 */
class _TResetPasswordHashes extends TTable {
    /**
     *
     * @param array $row
     * @return _T_RUsers_ResetPasswordHashes
     */
    static public function AssertRow(array $row): array {
        /* @phpstan-ignore return.type */
        return $row;
    }

    /**
     *
     * @param array $rows
     * @return array<_T_RUsers_ResetPasswordHashes>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }


    public function __construct(MDatabase $db, $tablePrefix = 'u_rph') {
        parent::__construct($db, 'Users_ResetPasswordHashes', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(true), 
            'DateTime' => new Database\FDateTime(true), 
            'Hash' => new Database\FString(true, 128), 
        ]);
        $this->setPKs([ 'Id' ]);

    }
}
