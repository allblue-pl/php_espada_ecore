<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class RTable_DBSync extends RRequest
{

    static public function Table_Select(EC\Database\TTable $table, array $args, 
            ?string &$error) : ?array
    {
        if (!array_key_exists('columnNames', $args))
            $args['columnNames'] = null;
        if (!array_key_exists('groupBy', $args))
            $args['groupBy'] = null;
        if (!array_key_exists('limit', $args))
            $args['limit'] = null;
        if (!array_key_exists('permitted', $args))
            $args['permitted'] = true;
        if (!array_key_exists('requestType', $args))
            $args['requestType'] = 'raw';
        if (array_key_exists('orderBy', $args))
            $args['orderBy'][] = [ '_Id', false ];
        else
            $args['orderBy'] = [[ '_Id', false ]];

        $queryExtension = '';

        if ($args['columnNames'] === null)
            $args['columnNames'] = $table->getColumnNames(true);
        else {
            foreach ($args['columnNames'] as $columnName) {
                if (!$table->hasColumn($columnName)) {
                    $error = "Column '{$columnName}' in 'columnNames'" .
                            " does not exist.";
                    return null;
                }
            }
        }

        if ($args['groupBy'] !== null) {
            $groupBy_ColNames_DB = [];
            foreach ($args['groupBy'] as $columnName) {
                if (!$table->hasColumn($columnName)) {
                    $error = "Column '{$columnName}' in 'groupBy' does not exist.";
                    return null;
                }

                $groupBy_ColNames_DB[] = $table->getDB()->quote($columnName);
            }

            $queryExtension .= ' GROUP BY ' . implode(',', $groupBy_ColNames_DB);
        }   

        $queryExtension .= " ORDER BY";
        $firstOrderBy = true; 
        foreach ($args['orderBy'] as $orderBy) {
            if ($firstOrderBy)
                $firstOrderBy = false;
            else
                $queryExtension .= ',';

            $queryExtension .= ' ' . $orderBy[0] . ' ' . 
                    ($orderBy[1] === true ? 'DESC' : 'ASC');
        }

        if ($args['limit'] !== null) {
            $offset = $args['limit'][0] === null ? 
                    0 : floor((float)$args['limit'][0]);
            $limit = $args['limit'][1] === null ? 
                    '18446744073709551615' : floor((float)$args['limit'][1]);

            $queryExtension .= " LIMIT {$offset}, {$limit}";
        }

        $rows = $table->select_Columns_Where($args['columnNames'], $args['where'], 
                $queryExtension);

        return $rows;
    }


    /* RRequest */
    public function getDeviceRowIds(EC\ABData\CDevice $device) : array
    {
        $rows = (new TDeviceRows($this->db))->select_Where([
            [ 'DeviceId', '=', $device->getId() ],
            [ 'TableId', '=', EC\HABData::GetTableId($this->table) ],
        ]);

        return array_column($rows, '_Id');
    }
    /* / RRequest */

}