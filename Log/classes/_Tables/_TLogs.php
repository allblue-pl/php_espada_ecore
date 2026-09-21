<?php namespace EC\Log\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_TRLog_Logs array{
 *     Id: float,
 *     User_Id: float|null,
 *     DateTime: float|null,
 *     Message: string|null,
 *     Data: string|null,
 * }
 * @phpstan-type _T_TRLog_Logs_Insert array{
 *     Id: float|null,
 *     User_Id: float|null,
 *     DateTime: float|null,
 *     Message: string|null,
 *     Data: string|null,
 * }
 * @phpstan-type _T_TRLog_Logs_Update array{
 *     Id?: float|null,
 *     User_Id?: float|null,
 *     DateTime?: float|null,
 *     Message?: string|null,
 *     Data?: string|null,
 * }
 * @phpstan-type _T_TRLog_Logs_Variant array{
 *     Id: float,
 *     User_Id: float|null,
 *     DateTime: float|null,
 *     Message: string|null,
 *     Data: string|null,
 *     ...<string,mixed>}
 */
class _TLogs extends TTable {
    /**
     *
     * @param _T_TRLog_Logs $row
     * @return _T_TRLog_Logs
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRLog_Logs_Insert $row
     * @return _T_TRLog_Logs_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRLog_Logs_Update $row
     * @return _T_TRLog_Logs_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRLog_Logs> $rows
     * @return list<_T_TRLog_Logs>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRLog_Logs|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRLog_Logs>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }

    /**
     *
     * @param _T_TRLog_Logs_Variant $row
     * @return _T_TRLog_Logs
     */
    static public function RawRow(MDatabase $db, array $row): array {
        $table = new _TLogs($db);

        /* @phpstan-ignore return.type */
        return $table->stripRow($row);
    }


    public function __construct(MDatabase $db, $tablePrefix = 'l_l') {
        parent::__construct($db, 'Log_Logs', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FLong(true), 
            'User_Id' => new Database\FLong(false), 
            'DateTime' => new Database\FLong(false), 
            'Message' => new Database\FString(false, 256), 
            'Data' => new Database\FText(false, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRLog_Logs|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRLog_Logs|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRLog_Logs|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRLog_Logs>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRLog_Logs>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRLog_Logs
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
