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
 * @phpstan-type _T_TRUsers_ResetPasswordHashes array{
 *     Id: float,
 *     User_Id: float,
 *     DateTime: int,
 *     Hash: string,
 * }
 * @phpstan-type _T_TRUsers_ResetPasswordHashes_Insert array{
 *     Id: float|null,
 *     User_Id: float,
 *     DateTime: int,
 *     Hash: string,
 * }
 * @phpstan-type _T_TRUsers_ResetPasswordHashes_Update array{
 *     Id?: float|null,
 *     User_Id?: float,
 *     DateTime?: int,
 *     Hash?: string,
 * }
 * @phpstan-type _T_TRUsers_ResetPasswordHashes_Variant array{
 *     Id: float,
 *     User_Id: float,
 *     DateTime: int,
 *     Hash: string,
 *     ...<string,mixed>}
 */
class _TResetPasswordHashes extends TTable {
    /**
     *
     * @param _T_TRUsers_ResetPasswordHashes $row
     * @return _T_TRUsers_ResetPasswordHashes
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRUsers_ResetPasswordHashes_Insert $row
     * @return _T_TRUsers_ResetPasswordHashes_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRUsers_ResetPasswordHashes_Update $row
     * @return _T_TRUsers_ResetPasswordHashes_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRUsers_ResetPasswordHashes> $rows
     * @return list<_T_TRUsers_ResetPasswordHashes>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRUsers_ResetPasswordHashes|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRUsers_ResetPasswordHashes>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }

    /**
     *
     * @param _T_TRUsers_ResetPasswordHashes_Variant $row
     * @return _T_TRUsers_ResetPasswordHashes
     */
    static public function RawRow(MDatabase $db, array $row): array {
        $table = new _TResetPasswordHashes($db);

        return $table->stripRow($row);
    }


    public function __construct(MDatabase $db, $tablePrefix = 'u_rph') {
        parent::__construct($db, 'Users_ResetPasswordHashes', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true), 
            'User_Id' => new Database\FLong(true), 
            'DateTime' => new Database\FDateTime(true), 
            'Hash' => new Database\FString(true, 128), 
        ]);
        $this->setPKs([ 'Id' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRUsers_ResetPasswordHashes|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRUsers_ResetPasswordHashes|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRUsers_ResetPasswordHashes|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRUsers_ResetPasswordHashes>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRUsers_ResetPasswordHashes>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRUsers_ResetPasswordHashes
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
