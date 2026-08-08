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
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Users_ResetPasswordHashes', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(true), 
            'DateTime' => new Database\FDateTime(true), 
            'Hash' => new Database\FString(true, 128), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RUsers_ResetPasswordHashes
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
     * @return array<_T_RUsers_ResetPasswordHashes>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
