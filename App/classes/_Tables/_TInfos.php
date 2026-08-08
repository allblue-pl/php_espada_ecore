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
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'App_Infos', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(true), 
            'AuthenticationHash' => new Database\FString(true, 256), 
            'Data' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RApp_Infos
     */
    public function assertRow(array $row, bool $stripRow = false): array {
        if ($stripRow)
            $row = $this->stripRow($row);

        /* @phpstan-ignore return.type */
        return $row;
    }

    /**
     *
     * @param array $rows
     * @return array<_T_RApp_Infos>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
