<?php namespace EC\App\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_TRApp_Infos array{
 *     Id: float,
 *     User_Id: float,
 *     AuthenticationHash: string,
 *     Data: string,
 * }
 * @phpstan-type _T_TRApp_Infos_Insert array{
 *     Id: float|null,
 *     User_Id: float,
 *     AuthenticationHash: string,
 *     Data: string,
 * }
 * @phpstan-type _T_TRApp_Infos_Update array{
 *     Id?: float|null,
 *     User_Id?: float,
 *     AuthenticationHash?: string,
 *     Data?: string,
 * }
 * @phpstan-type _T_TRApp_Infos_Variant array{
 *     Id: float,
 *     User_Id: float,
 *     AuthenticationHash: string,
 *     Data: string,
 *     ...<string,mixed>}
 */
class _TInfos extends TTable {
    /**
     *
     * @param _T_TRApp_Infos $row
     * @return _T_TRApp_Infos
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRApp_Infos_Insert $row
     * @return _T_TRApp_Infos_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRApp_Infos_Update $row
     * @return _T_TRApp_Infos_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRApp_Infos> $rows
     * @return list<_T_TRApp_Infos>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRApp_Infos|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRApp_Infos>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }


    public function __construct(MDatabase $db, $tablePrefix = 'a_i') {
        parent::__construct($db, 'App_Infos', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true), 
            'User_Id' => new Database\FLong(true), 
            'AuthenticationHash' => new Database\FString(true, 256), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRApp_Infos|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRApp_Infos|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRApp_Infos|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRApp_Infos>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRApp_Infos>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRApp_Infos
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
