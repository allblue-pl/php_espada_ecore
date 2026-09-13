<?php namespace EC\Users\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_TRUsers_Users array{
 *     Id: float,
 *     Type: int,
 *     LoginHash: string,
 *     EmailHash: string,
 *     PasswordHash: string,
 *     Groups: string,
 *     Active: bool,
 * }
 * @phpstan-type _T_TRUsers_Users_Insert array{
 *     Id: float,
 *     Type: int,
 *     LoginHash: string,
 *     EmailHash: string,
 *     PasswordHash: string,
 *     Groups: string,
 *     Active: bool,
 * }
 * @phpstan-type _T_TRUsers_Users_Update array{
 *     Id?: float,
 *     Type?: int,
 *     LoginHash?: string,
 *     EmailHash?: string,
 *     PasswordHash?: string,
 *     Groups?: string,
 *     Active?: bool,
 * }
 * @phpstan-type _T_TRUsers_Users_Variant array{
 *     Id: float,
 *     Type: int,
 *     LoginHash: string,
 *     EmailHash: string,
 *     PasswordHash: string,
 *     Groups: string,
 *     Active: bool,
 *     ...<string,mixed>}
 */
class _TUsers extends TTable {
    /**
     *
     * @param _T_TRUsers_Users $row
     * @return _T_TRUsers_Users
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRUsers_Users_Insert $row
     * @return _T_TRUsers_Users_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRUsers_Users_Update $row
     * @return _T_TRUsers_Users_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRUsers_Users> $rows
     * @return list<_T_TRUsers_Users>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRUsers_Users|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRUsers_Users>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }


    public function __construct(MDatabase $db, $tablePrefix = 'u_u') {
        parent::__construct($db, 'Users_Users', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true), 
            'Type' => new Database\FInt(true, false), 
            'LoginHash' => new Database\FString(true, 256), 
            'EmailHash' => new Database\FString(true, 256), 
            'PasswordHash' => new Database\FString(true, 256), 
            'Groups' => new Database\FString(true, 128), 
            'Active' => new Database\FBool(true), 
        ]);
        $this->setPKs([ 'Id' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRUsers_Users|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRUsers_Users|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRUsers_Users|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRUsers_Users>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRUsers_Users>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRUsers_Users
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
