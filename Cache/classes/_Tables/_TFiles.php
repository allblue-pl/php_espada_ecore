<?php namespace EC\Cache\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RCache_Files array{
 *     Id: int|null,
 *     User_Id: float|null,
 *     Hash: string,
 *     Expires: float,
 * }
 */
class _TFiles extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Cache_Files', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true, true), 
            'User_Id' => new Database\FLong(false), 
            'Hash' => new Database\FString(true, 128), 
            'Expires' => new Database\FLong(true), 
        ]);
        $this->setPKs([ 'Id' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RCache_Files
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
     * @return array<_T_RCache_Files>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
