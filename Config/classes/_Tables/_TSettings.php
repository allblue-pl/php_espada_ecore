<?php namespace EC\Config\_Tables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

/**
 *
 * @phpstan-type _T_RConfig_Settings array{
 *     Name: string,
 *     Value: string,
 * }
 */
class _TSettings extends TTable {
    public function __construct(MDatabase $db, $tablePrefix = 't') {
        parent::__construct($db, 'Config_Settings', $tablePrefix);

        $this->setColumns([
            'Name' => new Database\FString(true, 32), 
            'Value' => new Database\FText(true, 'medium'), 
        ]);
        $this->setPKs([ 'Name' ]);
    }

    /**
     *
     * @param array $row
     * @return _T_RConfig_Settings
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
     * @return array<_T_RConfig_Settings>
     */
    public function assertRows(array $rows): array {
        return $rows;
    }
}
