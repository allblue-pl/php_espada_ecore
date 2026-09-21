<?php namespace EC\Cache\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_TRCache_Files array{
 *     Id: float,
 *     User_Id: float|null,
 *     Hash: string,
 *     Expires: float,
 * }
 * @phpstan-type _T_TRCache_Files_Insert array{
 *     Id: float|null,
 *     User_Id: float|null,
 *     Hash: string,
 *     Expires: float,
 * }
 * @phpstan-type _T_TRCache_Files_Update array{
 *     Id?: float|null,
 *     User_Id?: float|null,
 *     Hash?: string,
 *     Expires?: float,
 * }
 * @phpstan-type _T_TRCache_Files_Variant array{
 *     Id: float,
 *     User_Id: float|null,
 *     Hash: string,
 *     Expires: float,
 *     ...<string,mixed>}
 */
class _TFiles extends TTable {
    /**
     *
     * @param _T_TRCache_Files $row
     * @return _T_TRCache_Files
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRCache_Files_Insert $row
     * @return _T_TRCache_Files_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRCache_Files_Update $row
     * @return _T_TRCache_Files_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRCache_Files> $rows
     * @return list<_T_TRCache_Files>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRCache_Files|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRCache_Files>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }

    /**
     *
     * @param _T_TRCache_Files_Variant $row
     * @return _T_TRCache_Files
     */
    static public function RawRow(MDatabase $db, array $row): array {
        $table = new _TFiles($db);

        /* @phpstan-ignore return.type */
        return $table->stripRow($row);
    }


    public function __construct(MDatabase $db, $tablePrefix = 'c_f') {
        parent::__construct($db, 'Cache_Files', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true), 
            'User_Id' => new Database\FLong(false), 
            'Hash' => new Database\FString(true, 128), 
            'Expires' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'Id' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRCache_Files|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRCache_Files|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRCache_Files|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRCache_Files>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRCache_Files>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRCache_Files
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
