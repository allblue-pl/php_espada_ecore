<?php namespace EC\Session\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_TRSession_Sessions array{
 *     Id: string,
 *     Access: int|null,
 *     Data: string|null,
 * }
 * @phpstan-type _T_TRSession_Sessions_Insert array{
 *     Id: string,
 *     Access: int|null,
 *     Data: string|null,
 * }
 * @phpstan-type _T_TRSession_Sessions_Update array{
 *     Id?: string,
 *     Access?: int|null,
 *     Data?: string|null,
 * }
 * @phpstan-type _T_TRSession_Sessions_Variant array{
 *     Id: string,
 *     Access: int|null,
 *     Data: string|null,
 *     ...<string,mixed>}
 */
class _TSessions extends TTable {
    /**
     *
     * @param _T_TRSession_Sessions $row
     * @return _T_TRSession_Sessions
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRSession_Sessions_Insert $row
     * @return _T_TRSession_Sessions_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRSession_Sessions_Update $row
     * @return _T_TRSession_Sessions_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRSession_Sessions> $rows
     * @return list<_T_TRSession_Sessions>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRSession_Sessions|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRSession_Sessions>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }

    /**
     *
     * @param _T_TRSession_Sessions_Variant $row
     * @return _T_TRSession_Sessions
     */
    static public function RawRow(MDatabase $db, array $row): array {
        $table = new _TSessions($db);

        return $table->stripRow($row);
    }


    public function __construct(MDatabase $db, $tablePrefix = 's_s') {
        parent::__construct($db, 'Session_Sessions', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FString(true, 32), 
            'Access' => new Database\FInt(false, true), 
            'Data' => new Database\FText(false, 'regular'), 
        ]);
        $this->setPKs([ 'Id' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRSession_Sessions|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRSession_Sessions|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRSession_Sessions|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRSession_Sessions>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRSession_Sessions>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRSession_Sessions
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
