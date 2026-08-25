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
    /**
     *
     * @param array $row
     * @return _T_RUsers_Users
     */
    static public function AssertRow(array $row): array {
        /* @phpstan-ignore return.type */
        return $row;
    }

    /**
     *
     * @param array $rows
     * @return array<_T_RUsers_Users>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }


    public function __construct(MDatabase $db, $tablePrefix = 'u_u') {
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
}
