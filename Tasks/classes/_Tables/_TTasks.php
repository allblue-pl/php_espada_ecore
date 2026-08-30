<?php namespace EC\Tasks\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_RTasks_Tasks array{
 *     Hash: string,
 *     User_Id: float|null,
 *     DateTime: float,
 *     Finished: bool,
 *     Info: string,
 *     Data: string,
 * }
 */
class _TTasks extends TTable {
    /**
     *
     * @param _T_RTasks_Tasks $row
     * @return _T_RTasks_Tasks
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_RTasks_Tasks> $rows
     * @return list<_T_RTasks_Tasks>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_RTasks_Tasks|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_RTasks_Tasks>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }


    public function __construct(MDatabase $db, $tablePrefix = 't_t') {
        parent::__construct($db, 'Tasks_Tasks', $tablePrefix);

        $this->setColumns([
            'Hash' => new Database\FString(true, 128), 
            'User_Id' => new Database\FLong(false), 
            'DateTime' => new Database\FDateTime(true), 
            'Finished' => new Database\FBool(true), 
            'Info' => new Database\FText(true, 'medium'), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Hash' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_RTasks_Tasks|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_RTasks_Tasks|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_RTasks_Tasks|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_RTasks_Tasks>|null
     * @phpstan-ignore return.phpDocType
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_RTasks_Tasks>|null
     * @phpstan-ignore return.phpDocType
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_RTasks_Tasks
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
