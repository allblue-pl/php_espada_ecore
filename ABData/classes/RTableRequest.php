<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class RTableRequest extends RRequest {

    // static public $ArgNames = [ 'columnNames', 'groupBy', 'join', 'limit' ];


    static public function Table_Select(EC\Database\TTable $table, array $args, 
            ?string &$error) : ?array
    {
        // foreach ($args as $argName => $arg) {
        //     if (!in_array($argName, self::$ArgNames)) {
        //         $error = "Unknown arg '{$argName}'.";
        //         return null;
        //     }
        // }

        if (!array_key_exists('columnNames', $args))
            $args['columnNames'] = null;
        if (!array_key_exists('groupBy', $args))
            $args['groupBy'] = null;
        if (!array_key_exists('limit', $args))
            $args['limit'] = null;
        if (!array_key_exists('permitted', $args))
            $args['permitted'] = true;
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

    static public function ParseRequest(array $request)
    {
        
    }


    private $db = null;
    private $dataStore = null;
    private $table = null;

    public function __construct(CDataStore $dataStore, EC\Database\TTable $table)
    {
        parent::__construct($dataStore);

        $this->dataStore = $dataStore;
        $this->db = $this->dataStore->getDB();
        $this->table = $table;

        $this->setA('delete', function(CDevice $device, array $args, 
                ?int $schemeVersion) {
            return $this->action_Delete($device, $args, $schemeVersion);
        });
        $this->setA('row', function(CDevice $device, array $args, 
                ?int $schemeVersion) {
            return $this->action_Row($device, $args, $schemeVersion);
        });
        $this->setA('select', function(CDevice $device, array $args, 
                ?int $schemeVersion) {
            return $this->action_Select($device, $args, $schemeVersion);
        });
        // $this->setA('set', function(CDevice $device, array $args, 
        //         ?int $schemeVersion) {
        //     return $this->action_Set($device, $args, $schemeVersion);
        // });
        $this->setA('update', function(CDevice $device, array $args, 
                ?int $schemeVersion) {
            return $this->action_Update($device, $args, $schemeVersion);
        });
    }

    public function action_Delete(CDevice $device, array $args, ?int $schemeVersion)
    {
        $rows = $this->table->select_Where($args['where']);

        if (!HABData::Delete_Where($device, $this->tablem, $args['where'])) {
            return [
                'success' => false,
                'error' => EDEBUG ?
                    $this->db->getError() :
                    'Cannot delete rows.',
            ];
        }

        $rDeletedRows = [];
        foreach ($rows as $row) {
            $rDeletedRows[] = [
                'TableName' => $this->table->getTableName(),
                'Id' => $row['_Id'],
                'DateTime' => $device->getLastUpdate(),
                'DeviceId' => $device->getDeviceId(),
            ];
        }

        if (!(new TDeletedRows($this->db))->update($rDeletedRows)) {
            return [
                'success' => false,
                'error' => "Cannot update 'DeletedRows': " + $this->db->getError(),
            ];
        } 

        return [
            'success' => true,
            'error' => null,
        ];
    }

    public function action_Row(CDevice $device, array $args, ?int $schemeVersion)
    {
        return [
            'success' => true,
            'row' => $this->table->row_Where($args['where']),
            'error' => null,
        ];
    }

    public function action_Select(CDevice $device, array $args, ?int $schemeVersion)
    {
        if (!$args['permitted'])
            return [];

        $error = null;
        $rows = self::Table_Select($this->table, $args, $error);

        if ($args['join'] !== null) {
            $result = $this->join($device, $rows, $args);
            return $result;
        }

        return [
            'success' => $error !== null,
            'rows' => $rows,
            'error' => $error,
        ];
    }

    public function action_Update(CDevice $device, array $args, ?int $schemeVersion)
    {
        if (count($args['rows']) === 0) {
            return [
                'success' => true,
                'error' => null,
            ];
        }
        

        // $nextId = null;

        // $rows = [];
        foreach ($args['rows'] as &$args_Row) {
            if (!array_key_exists('_Id', $args_Row))
                throw new \Exception("No '_Id' column in rows.");

            $args_Row['_Modified_DateTime'] = $device->getCreateTime();
            $args_Row['_Modified_DeviceId'] = $device->getId();
        //     foreach ($args_Row as $column_Name => &$column_Value) {
        //         if ($column_Name === '_Id') {
        //             if ($column_Value === null)
        //                 $column_Value = $this->getNextId($nextId);
        //         }
        }

        //     $rows[] = $args_Row;
        // }

        $result = [
            'success' => EC\HABData::Update($device, $this->table, $args['rows']),
            'error' => null,
        ];

        $result['error'] = EDEBUG ?
                    $this->db->getError() :
                    'Cannot update rows.';

        return $result;
    }

    public function getDB()
    {
        return $this->db;
    }

    public function getTable()
    {
        return $this->table;
    }


    private function join(CDevice $device, array $rows, array $args)
    {
        foreach ($args['join'] as $join) {
            $tableRequest = $this->dataStore->getRequest($join['table']);

            if (!$this->getTable()->hasColumn($join['on'][0])) {
                return [
                    'success' => false,
                    'rows' => null,
                    'error' => "Column '{$join['on'][0]}' from 'on' join does not exist.",
                ];
            }

            if (!$tableRequest->getTable()->hasColumn($join['on'][1])) {
                return [
                    'success' => false,
                    'rows' => null,
                    'error' => "Column '{$join['on'][1]}' from 'on' join does not exist.",
                ];
            }

            $columnNames = null;
            if ($join['columns'] === null)
                $columnNames = $this->table->getColumnNames_Select();
            else {
                $columnNames = [];
                foreach ($args['columns'] as $columnName) {
                    if (!$this->table->hasColumn($columnName)) {
                        return [
                            'success' => false,
                            'rows' => null,
                            'error' => "Column '{$columnName}' in 'columns'" .
                                    " in join '{$join['table']}' does not exist.",
                        ];
                    }

                    $columnNames[] = $columnName;
                }
            }
            $columnNames = $tableRequest->getTable()->getColumnNames();

            $join_Rows = null;
            if (count($rows) > 0) {
                $on_ColValues = array_column($rows, $join['on'][0]);
                $result = $tableRequest->action_Select($device, [
                    'columns' => $columnNames,
                    'where' => [
                        [ $join['on'][1], 'IN', $on_ColValues ],
                    ],
                    'limit' => null,
                    'groupBy' => [ $join['on'][1] ],
                    'join' => null,
                ]);

                if (!$result['success']) {
                    return [
                        'success' => false,
                        'rows' => null,
                        'error' => "Join '{$join['table']}': " . $result['error'],
                    ];
                }

                $join_Rows = $result['rows'];
            } else
                $join_Rows = [];

            foreach ($rows as &$row) {
                $joinFound = false;

                foreach ($join_Rows as $join_Row) {
                    if ($row[$join['on'][0]] === $join_Row[$join['on'][1]]) {
                        $joinFound = true;

                        foreach ($join_Row as $columnName => $col)
                            $row[$join['prefix'] . $columnName] = $col;

                        continue;
                    }
                }

                if (!$joinFound) {
                    foreach ($columnNames as $columnName)
                        $row[$join['prefix'] . $columnName] = null;
                }
            }
        }

        return [
            'success' => true,
            'rows' => $rows,
            'error' => null,
        ];
    }

    // private function getNextId($nextId)
    // {
    //     if ($nextId !== null)
    //         return $nextId + 1;

    //     $rows_ForId = $this->db->query_Select('SELECT MAX(_Id) AS MaxId FROM ' . 
    //             $this->table->getTableName_Quoted() .
    //             ' WHERE _Id < 100000000 FOR UPDATE');
        
    //     if (count($rows_ForId) === 0)
    //         return 1;
        
    //     return $rows_ForId[0]['MaxId'] + 1;
    // }


    /* RRequest */
    public function getDeviceRowIds(CDevice $device) : array
    {
        $rows = (new TDeviceRows($this->db))->select_Where([
            [ 'DeviceId', '=', $device->getId() ],
            [ 'TableId', '=', HABData::GetTableId($this->table->getTableName()) ],
        ]);

        return array_column($rows, 'RowId');
    }
    /* / RRequest */

}