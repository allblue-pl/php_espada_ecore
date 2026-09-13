<?php namespace EC\Config\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABData\HABTablesHelper;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;
use Override;

/**
 *
 * @phpstan-type _T_TRConfig_Settings array{
 *     Name: string,
 *     Value: string,
 * }
 * @phpstan-type _T_TRConfig_Settings_Insert array{
 *     Name: string,
 *     Value: string,
 * }
 * @phpstan-type _T_TRConfig_Settings_Update array{
 *     Name?: string,
 *     Value?: string,
 * }
 * @phpstan-type _T_TRConfig_Settings_Variant array{
 *     Name: string,
 *     Value: string,
 *     ...<string,mixed>}
 */
class _TSettings extends TTable {
    /**
     *
     * @param _T_TRConfig_Settings $row
     * @return _T_TRConfig_Settings
     */
    static public function AssertRow(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRConfig_Settings_Insert $row
     * @return _T_TRConfig_Settings_Insert
     */
    static public function AssertRow_Insert(array $row): array {
        return $row;
    }

    /**
     *
     * @param _T_TRConfig_Settings_Update $row
     * @return _T_TRConfig_Settings_Update
     */
    static public function AssertRow_Update(array $row): array {
        return $row;
    }

    /**
     *
     * @param list<_T_TRConfig_Settings> $rows
     * @return list<_T_TRConfig_Settings>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }

    // /**
    //  *
    //  * @param array|null $row
    //  * @return _T_TRConfig_Settings|null
    //  */
    // static public function CastRow(array|null $row): array|null {
    //     /* phpstan-ignore return.type */
    //     return $row;
    // }

    // /**
    //  *
    //  * @param array $rows
    //  * @return list<_T_TRConfig_Settings>
    //  */
    // static public function CastRows(array $rows): array {
    //     return $rows;
    // }


    public function __construct(MDatabase $db, $tablePrefix = 'c_s') {
        parent::__construct($db, 'Config_Settings', $tablePrefix);

        $this->setColumns([
            'Name' => new Database\FString(true, 32), 
            'Value' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Name' ]);


        HABTablesHelper::SetTableVFields($this);
    }

    /** 
     * @return _T_TRConfig_Settings|null
     */
     #[Override]
    public function row_ByColumn(string $colName, mixed $colValue, 
            string $groupExtension = '', bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByColumn($colName, $colValue, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRConfig_Settings|null
     */
    #[Override]
    public function row_ByPKs(array $keys, string $groupExtension = '', 
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_ByPKs($keys, $groupExtension, $forUpdate);
    }

    /** 
     * @return _T_TRConfig_Settings|null
     */
    #[Override]
    public function row_Where(array $conditions = [], string $groupExtension = '',
            bool $forUpdate = false): array|null {
        /* @phpstan-ignore return.type */
        return parent::row_Where($conditions, $groupExtension, $forUpdate);
    }

    /** 
     * @return list<_T_TRConfig_Settings>
     */
    #[Override]
    public function select_ByPKs(array $pks, string $groupExtension = ''): array {
        return parent::select_ByPKs($pks, $groupExtension);
    }

    /** 
     * @return list<_T_TRConfig_Settings>
     */
    #[Override]
    public function select_Where(array $conditions = [], string $groupExtension = '',
            bool $tableOnly = false): array {
        return parent::select_Where($conditions, $groupExtension);
    }

    /** 
     * @return _T_TRConfig_Settings
     */
    #[Override]
    public function stripRow_TableColumnsOnly(array $row): array {
        /* @phpstan-ignore return.type */
        return parent::stripRow_TableColumnsOnly($row);
    }
}
