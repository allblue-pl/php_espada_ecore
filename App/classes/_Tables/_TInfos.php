<?php namespace EC\App\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RApp_Infos array{
 *     Id: int|null,
 *     User_Id: float,
 *     AuthenticationHash: string,
 *     Data: string,
 * }
 */
class _TInfos extends TTable {
    /**
     *
     * @param array $row
     * @return _T_RApp_Infos
     */
    static public function AssertRow(array $row): array {
        /* @phpstan-ignore return.type */
        return $row;
    }

    /**
     *
     * @param array $rows
     * @return array<_T_RApp_Infos>
     */
    static public function AssertRows(array $rows): array {
        return $rows;
    }


    public function __construct(MDatabase $db, $tablePrefix = 'a_i') {
        parent::__construct($db, 'App_Infos', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(true), 
            'AuthenticationHash' => new Database\FString(true, 256), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);

    }
}
