<?php namespace EC\Users\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RUsers_Users array{
 *     Id: float,
 *     Type: string,
 *     LoginHash: string,
 *     EmailHash: string,
 *     PasswordHash: string,
 *     Groups: string,
 *     Active: bool,
 * }
 */
class _TUsers extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Users_Users', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true), 
            'Type' => new Database\FString(true, 16), 
            'LoginHash' => new Database\FString(true, 256), 
            'EmailHash' => new Database\FString(true, 256), 
            'PasswordHash' => new Database\FString(true, 256), 
            'Groups' => new Database\FString(true, 128), 
            'Active' => new Database\FBool(true), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RUsers_Users
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
     * @return array<_T_RUsers_Users>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
