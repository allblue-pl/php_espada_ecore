<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class TTable {

    private $db = null;

    private $name = null;
    private $alias = '';
    private $prefix = '';

    private $join = null;
    private $groupBy = null;

    public $columns = [];
    private $columns_Table = [];

    private $primaryKeys = [ 'Id' ];

    private $selectColumnNames = null;

    private $rowParsers = [];

    private $lastInsertedId = null;

    public function __construct(EC\MDatabase $db, $table_name,
            $table_alias = null) {
        $this->db = $db;

        $this->name = $table_name;
        $this->alias = $table_alias;
        if ($table_alias !== null)
            $this->prefix = $table_alias . '.';

        $this->addColumns_Optional([
            'Count' => [ 'COUNT(*)', new FInt(true, 11) ]
        ]);
    }

    public function addColumnParser($columnName, array $parser) {
        $column = &$this->getColumnRef($columnName);

        foreach ($parser as $parser_type => $parser_info) {
            if ($parser_type !== 'in' && $parser_type !== 'out')
                throw new \Exception('Wrong `parser` format.');
        }

        $column['parsers'][] = [
            'out' => array_key_exists('out', $parser) ? $parser['out'] : null,
            'in' => array_key_exists('in', $parser) ? $parser['in'] : null,
        ];
    }

    public function addColumnVFields($columnName, array $vFields) {
        $column = &$this->getColumnRef($columnName);
        foreach ($vFields as $vField)
            $column['vFields'][] = $vField;
    }

    public function addColumns($column_infos, $extra = false, $optional = false) {
        foreach ($column_infos as $columnName => $column_info) {
            if (array_key_exists($columnName, $this->columns))
                throw new \Exception("Column `{$columnName}` already exists.");

            $column = &$this->parseColumnInfo($columnName, $column_info, $extra, $optional);

            $this->columns[$columnName] = &$column;

            if (!$extra)
                $this->columns_Table[$columnName] = &$column;

            if (!$optional)
                $this->selectColumnNames[] = $columnName;
        }
    }

    public function addColumns_Extra($columns) {
        $this->addColumns($columns, true);
    }

    public function addColumns_Ref(TTable $table, $refColumnInfos, $extra = true) {
        foreach ($refColumnInfos as $columnName => $ref_column_info) {
            $column_expr = $ref_column_info[0];
            $ref_column = $table->getColumn($ref_column_info[1]);

            if ($this->columnExists($columnName)) {
                if ($extra)
                    continue;

                $column = &$this->getColumnRef($columnName);

                $column['parsers'] = $ref_column['parsers'];
                $column['vFields'] = $ref_column['vFields'];

                continue;
            }

            $column_info = [ $column_expr, $ref_column['field'] ];
            $this->addColumns([ $columnName => $column_info ], $extra);

            $column = &$this->getColumnRef($columnName);

            $column['parsers'] = $ref_column['parsers'];
            $column['vFields'] = $ref_column['vFields'];
        }
    }

    public function addColumns_Ref_All(TTable $table, $fieldPrefix = '',
            $excludedColumns = [], $includedColumns = null) {
        $this->addColumns_Ref($table, $table->getColumnTableRefs( 
                $fieldPrefix, $excludedColumns, $includedColumns));
    }

    public function addColumns_Optional($columns, $extra = true) {
        $this->addColumns($columns, $extra, true);
    }

    public function addJoin(string $join) {
        $this->join .= ($join[0] !== ' ' ? ' ' : '') . $join;
    }

    public function addRowParser($parser) {
        $this->rowParsers[] = $parser;
    }

    public function clearColumnParsers($columnName) {
        $column = &$this->getColumnRef($columnName);

        $column['parsers'] = [];
    }

    public function checkColumns() {
        if (count($this->columns_Table) > 0)
            return;

        $class_name = get_called_class();

        throw new \Exception("Table columns not set in `{$class_name}.");
    }

    public function columnExists($columnName, $is_table_column = false) {
        if ($is_table_column)
            return array_key_exists($columnName, $this->columns_Table);
        else
            return array_key_exists($columnName, $this->columns);
    }

    public function count($group_extension = '') {
        $query = "SELECT COUNT(DISTINCT {$this->prefix}Id) as count" .
                ' FROM ' . $this->getQuery_From();

        if ($group_extension !== '')
            $query .= ' ' . $group_extension;
        $query .= ' LIMIT 1';

        $rows = $this->db->query_Select($query);

        if (count($rows) === 0)
            return null;

        return $this->db->unescapeInt($rows[0]['count']);
    }

    public function count_Where($where_conditions, $group_extension = '') {
        $where = $this->getQuery_Conditions($where_conditions);
        if ($where !== '')
            $group_extension = 'WHERE ' . $where . ' ' . $group_extension;

        return $this->count($group_extension);
    }

    public function delete($query_extension = '') {
        $query = 'DELETE ' . $this->db->quote($this->alias) . ' FROM ' .
                $this->getQuery_From();

        if ($query_extension !== '')
            $query .= ' ' . $query_extension;

        return $this->db->query_Execute($query);
    }

    public function delete_ByColumn($colName, $colValue) {
        return $this->delete_Where([
            [ $colName, '=', $colValue ]
        ]);
    }

    public function delete_ById($id) {
        return $this->delete_Where([
            [ 'Id', '=', $id ]
        ]);
    }

    public function delete_ByPKs(array $keys) {
        $where = [];
        if (count($keys) !== count($this->primaryKeys)) {
            throw new \Exception('Keys do not match primary keys: ' . 
                    join(',', $this->primaryKeys));
        }

        $keys_Where = [ 'AND', [] ];
        for ($i = 0; $i < count($keys); $i++)
            $keys_Where[1][] = [ $this->primaryKeys[$i], '=', $keys[$i] ];
            
        $where[] = $keys_Where;

        return $this->delete_Where($where);
    }

    public function delete_Where($conditions) {
        if (count($conditions) === 0)
            return true;

        return $this->delete('WHERE ' . $this->getQuery_Conditions($conditions));
    }

    public function escapeColumnValue(string $columnName, $value) {
        $column = $this->getColumn($columnName);

        if ($value instanceof CRawValue)
            return $value->getValue();

        return $column['field']->escape($this->db, $value);
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getColumn($columnName, $tableOnly = false) {
        return $this->getColumnRef($columnName, $tableOnly);
    }

    public function &getColumnRef($columnName, $tableOnly = false) {
        // if ($columnName === 'Cow_Nr') {
        //     echo $columnName . '#' . $tableOnly . "\r\n";
        //     print_r($this->columns_Table);
        //     echo array_key_exists($columnName, $this->columns_Table) . "\r\n";
        // }

        if (!array_key_exists($columnName, $this->columns)) {
            $class_name = get_called_class();
            throw new \Exception("Column `{$columnName}` does not exist" .
                    " in {$class_name}.");
        }

        if ($tableOnly && !array_key_exists($columnName, $this->columns_Table)) {
            $class_name = get_called_class();
            throw new \Exception("Column `{$columnName}` is not table column" .
                    " in {$class_name}.");
        }

        return $this->columns[$columnName];
    }

    public function getColumnNames($tableOnly = false, $prefix = null) {
        $columnNames = null;

        if ($tableOnly)
            $columnNames = array_keys($this->columns_Table);
        else {
            $columnNames = [];
            foreach ($this->columns as $columnName => $column) {
                if (!$column['optional'])
                    $columnNames[] = $columnName;
            }
        }

        if ($prefix !== null) {
            for ($i = 0; $i < count($columnNames); $i++)
                $columnNames[$i] = $prefix . $columnNames[$i];
        }

        return $columnNames;
    }

    public function getColumnNames_Select() {
        return $this->selectColumnNames;
    }

    public function getColumnTableRefs($fieldPrefix = '',
            $excludedColumns = [ 'Id' ], $includedColumns = null, 
            $tableOnly = false) {
        $columns = $tableOnly ? $this->columns_Table : $this->columns;

        $columnRefs = [];
        foreach ($columns as $columnName => $columnField) {
            $column = $this->getColumn($columnName);
            if ($column['optional'])
                continue;

            if ($excludedColumns !== null) {
                if (in_array($column['name'], $excludedColumns))
                    continue;
            } else if ($includedColumns !== null) {
                if (!in_array($column['name'], $includedColumns))
                    continue;
            }

            $columnRefs[$fieldPrefix . $column['name']] =
                    [ $column['expr'], $column['name'] ];
        }

        return $columnRefs;
    }

    public function getColumnTableRefs_TableOnly($fieldPrefix = '',
            $excludedColumns = [ 'Id' ], $includedColumns = null) {
        return $this->getColumnTableRefs($fieldPrefix, $excludedColumns, 
                $includedColumns, true);
    }

    public function getDB() {
        return $this->db;
    }

    public function getLastInsertedId() {
        return $this->lastInsertedId;
    }

    public function getJoin() {
        return $this->join;
    }

    public function getPKs() {
        return $this->primaryKeys;
    }

    public function getTableName() {
        return $this->name;
    }

    public function getTableName_Quoted() {
        return $this->db->quote($this->name);
    }

    public function getQuery_Conditions($column_values, $tableOnly = false) {
        return $this->getQuery_Conditions_Helper($column_values, 'AND', 
                $tableOnly);
    }

    public function getQuery_From() {
        $query = $this->getTableName_Quoted();
        if ($this->alias !== null)
            $query .= " AS {$this->alias}";

        if ($this->join !== null)
            $query .= ' ' . $this->join;

        return $query;
    }

    public function getQuery_Select($columnNames = null) {
        if ($columnNames === null)
            $columnNames = $this->selectColumnNames;

        if (count($columnNames) === 0)
            throw new \Exception('Select columns cannot be empty.');

        $column_selects = [];
        foreach ($columnNames as $columnName) {
            $column_select = $this->getQuery_SelectColumn($columnName);
            if ($column_select === null)
                continue;

            $column_selects[] = $column_select;
        }

        return implode(', ', $column_selects);
    }


    public function getQuery_SelectColumn($columnName) {
        $column = $this->getColumn($columnName);

        $db_column_name = $this->db->quote($columnName);
        $db_column_expr = $column['expr'];

        if ($db_column_expr === null)
            return null;

        return "{$db_column_expr} AS {$db_column_name}";
    }

    public function hasColumn($columnName) {
        return array_key_exists($columnName, $this->columns);
    }

    public function insert(array $rows, bool $updateColumns = true) {
        $this->checkColumns();

        if (count($rows) === 0)
            return true;

        /* Column Names */
        $columnNames = $this->getColumnNames(true);

        $columnNames_DB = [];
        foreach ($columnNames as $columnName)
            $columnNames_DB[] = $this->db->quote($columnName);
        $columnNames_DB_Str = implode(',', $columnNames_DB);

        /* Values */
        $valuesArr_DB = [];
        foreach ($rows as $row) {
            $row_DB = [];
            foreach ($row as $columnName => $columnValue) {
                $row_DB[] = $this->escapeColumn($row, 
                        $this->getColumn($columnName), $columnValue);
            }

            $valuesArr_DB[] = '(' . implode(',', $row_DB) . ')';
        }
        $db_values = implode(',', $valuesArr_DB);

        /* Update Columns */
        $query = "INSERT INTO {$this->name} ({$columnNames_DB_Str})" .
                " VALUES {$db_values}";

        if ($updateColumns) {
            $db_update_columns_array = [];
            foreach ($columnNames_DB as $db_col_name)
                $db_update_columns_array[] = "{$db_col_name} = VALUES($db_col_name)";
            $db_update_columns = implode(',', $db_update_columns_array);

            $query .= " ON DUPLICATE KEY UPDATE {$db_update_columns}";
        }

        $this->lastInsertedId = $this->db->getLastInsertedId();

        return $this->db->query_Execute($query);
    }

    public function parseColumnValue($columnName, $value) {
        $column = $this->getColumn($columnName);

        return $column['field']->unescape($this->db, 
                $column['field']->escape($this->db, $value));
    }

    public function parseRow($row, $columnNames = null) {
        $this->checkColumns();

        $unescaped_row = [];
        foreach ($row as $columnName => $db_column_value) {
            if (!$this->columnExists($columnName)) {
                $parsed_row[$columnName] = $db_column_value;
                continue;
            }

            $column = $this->getColumn($columnName);
            $unescaped_row[$columnName] = $column['field']->unescape(
                $this->db, $db_column_value);
        }

        $parsed_row = $row;
        foreach ($row as $columnName => $db_column_value) {
            if (!$this->columnExists($columnName)) {
                // $parsed_row[$columnName] = $db_column_value;
                continue;
            }

            $column = $this->getColumn($columnName);

            if (array_key_exists('parsers', $column)) {
                $continue = false;

                foreach ($column['parsers'] as $column_parser) {
                    if ($column_parser['out'] !== null) {
                        $parsed_cols = $column_parser['out']($row, $columnName,
                                $unescaped_row[$columnName], $parsed_row);
                        if (!is_array($parsed_cols)) {
                            throw new \Exception('Column parser must return row as an array.');
                        }
                        foreach ($parsed_cols as $parsed_col_name => $parsed_col_value) {
                            if ($parsed_col_name !== $columnName) {
                                if ($columnNames !== null) {
                                    if (!in_array($parsed_col_name, $columnNames))
                                        continue;
                                }

                                if ($this->columnExists($parsed_col_name, true)) {
                                    throw new \Exception('Cannot modify existing' .
                                            ' columns inside column parsed.');
                                }

                                if (!$this->columnExists($parsed_col_name, false)) {
                                    throw new \Exception('Cannot modify undeclared ' .
                                            "column `{$parsed_col_name}` in `" .
                                            get_called_class() .  '`.');
                                }
                            }

                            $parsed_row[$parsed_col_name] = $parsed_col_value;
                        }

                        if (!array_key_exists($columnName, $parsed_row)) {
                            throw new \Exception("Column `{$columnName}` not set" .
                                    ' in its column parser.');
                        }


                        $continue = true;
                    }
                }

                if ($continue)
                    continue;
            }

            $parsed_row[$columnName] = $unescaped_row[$columnName];
        }

        return $parsed_row;

        // foreach ($this->columns as $columnName =>
        //         list($columnName, $column)) {
        //
        //
        //     if (isset($row[$columnName])) {
        //         $columnValueue = $column->unescape($this->db, $row[$columnName]);
        //     }
        //         $row[$columnName] = $column->unescape($this->db, $row[$columnName]);
        // }

        // return $this->_parseRow($row);
    }

    public function parseRows($rows, $columns = null) {
        $parsed_rows = [];
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $parsed_row = $this->parseRow($row, $columns);
            foreach ($this->rowParsers as $row_parser)
                $row_parser($parsed_row, $i);

            $parsed_rows[] = $parsed_row;
        }

        return $parsed_rows;
    }

    public function row($query_extension = '', $group_extension = '',
            $for_update = false) {
        if ($group_extension !== '')
            $group_extension .= ' ';
        $group_extension .= 'LIMIT 1';

        if ($for_update)
            $group_extension .= ' FOR UPDATE';

        $rows = $this->select($query_extension, $group_extension);

        if (count($rows) === 0)
            return null;

        return $rows[0];
    }

    public function row_ByColumn($colName, $colValue, $group_extension = '', $for_update = false) {
        return $this->row_Where([
            [ $colName, '=', $colValue ]
        ], $group_extension, $for_update);
    }

    public function row_ById($id, $group_extension = '', $for_update = false) {
        return $this->row_Where([
            [ 'Id', '=', $id ]
        ], $group_extension, $for_update);
    }

    public function row_ByPKs(array $keys, $groupExtension = '', 
            $forUpdate = false) {
        $where = [];
        if (count($keys) !== count($this->primaryKeys)) {
            throw new \Exception('Keys do not match primary keys: ' . 
                    join(',', $this->primaryKeys));
        }

        $keys_Where = [ 'AND', [] ];
        for ($i = 0; $i < count($keys); $i++)
            $keys_Where[1][] = [ $this->primaryKeys[$i], '=', $keys[$i] ];
            
        $where[] = $keys_Where;

        return $this->row_Where($where, $groupExtension, $forUpdate);
    }

    public function row_ByPK(array $keys, $group_extension = '', $for_update = false) {
        if (count($keys) !== count($this->primaryKeys)) {
            throw new \Exception('Keys do not match primary keys: ' . 
                    join(',', $this->primaryKeys));
        }

        $where = [];
        for ($i = 0; $i < count($keys); $i++)
            $where[] = [ $this->primaryKeys[$i], '=', $keys[i] ];

        return $this->row_Where($where, $group_extension, $for_update);
    }

    public function row_Columns($columnNames, $query_extension = '',
            $group_extension = '', $for_update = false) {
        $select = $this->getQuery_Select($columnNames);

        if ($group_extension !== '')
            $group_extension .= ' ';
        $group_extension .= 'LIMIT 1';

        if ($for_update)
            $group_extension .= ' FOR UPDATE';

        $rows = $this->select_Columns($columnNames, $query_extension,
                $group_extension);

        if (count($rows) === 0)
            return null;

        return $rows[0];
    }

    public function row_Columns_ById($columnNames, $id, $group_extension = '', 
            $for_update = false) {
        return $this->row_Columns_Where($columnNames, [
            [ 'Id', '=', $id ]
        ], $group_extension, $for_update);
    }

    public function row_Columns_Where($columnNames, $where_conditions,
            $group_extension = '', $for_update = false) {
        $query_extension = '';

        $where = $this->getQuery_Conditions($where_conditions);
        if ($where !== '')
            $query_extension = 'WHERE ' . $where;

        return $this->row_Columns($columnNames, $query_extension, $group_extension);
    }

    public function row_Where($args = [], $group_extension = '',
            $for_update = false) {
        $where = '';

        $conditions = $this->getQuery_Conditions($args);
        if ($conditions !== '')
            $where .= 'WHERE ' . $conditions;

        return $this->row($where, $group_extension, $for_update);
    }

    public function select($query_extension = '', $group_extension = '') {
        $selectColumnNames = $this->selectColumnNames;

        return $this->select_Columns($selectColumnNames, $query_extension,
                $group_extension);
    }

    public function select_ByPKs(array $pks, string $groupExtension = '') {
        if (count($pks) === 0)
            return [];

        $rows = [];

        $primaryKeys_Escaped = [];
        foreach ($this->primaryKeys as $pk)
            $primaryKeys_Escaped[] = $this->alias . '.' . $pk;

        $queryExtension = 'WHERE (' . implode(',', $primaryKeys_Escaped) . ') IN ';
        $pks_StrArr = [];
        foreach ($pks as $keys) {
            if (!is_array($keys))
                throw new \Exception("'keyPairs' must be an array of arrays.");

            if (count($keys) !== count($this->primaryKeys)) {
                throw new \Exception('Keys do not match primary keys: ' . 
                        join(',', $this->primaryKeys));
            }

            $keys_Escaped = [];
            for ($i = 0; $i < count($keys); $i++) {
                $keys_Escaped[] = $this->escapeColumn($keys, 
                        $this->getColumn($this->primaryKeys[$i]), $keys[$i]);
            }
            $pks_StrArr[] = '(' . implode(',', $keys_Escaped) . ')';
        }

        $queryExtension .= '(' . implode(',', $pks_StrArr) . ')';

        $rows = $this->select($queryExtension, $groupExtension);

        /* Universal Alternative */
        // for ($i = 0; $i < count($pks); $i += 100) {
        //     $where = [ 'OR', [] ];
        //     for ($j = $i; $j < min($i + 100, count($pks)); $j++) {
        //         $keys = $pks[$j];
        //         if (!is_array($keys))
        //             throw new \Exception("'keyPairs' must be an array of arrays.");

        //         if (count($keys) !== count($this->primaryKeys)) {
        //             throw new \Exception('Keys do not match primary keys: ' . 
        //                     join(',', $this->primaryKeys));
        //         }

        //         $keys_Where = [ 'AND', [] ];
        //         for ($k = 0; $k < count($keys); $k++)
        //             $keys_Where[1][] = [ $this->primaryKeys[$k], '=', $keys[$k] ];
                    
        //         $where[1][] = $keys_Where;
        //     }

            // if ($this->getTableName() === '_ABData_DeletedRows' && $i >= 0) {
            //     echo "Before";
            //     print_r(count($where[1]));
            //     die;
            // }

        //     $rows_New = $this->select($queryExtension, $groupExtension);
        //     $rows = array_merge($rows, $rows_New);

        //     if ($this->getTableName() === '_ABData_DeletedRows' && $i >= 0) {
        //         echo $this->db->getLastQuery();
        //         echo "Break";
        //         die;
        //     }
        // }
        /* / Universal Alternative */    

        return $rows;
    }

    public function select_Columns($columnNames, $query_extension = '',
            $group_extension = '') {
        $select = $this->getQuery_Select($columnNames);

        return $this->select_Custom($select, $this->getQuery_From(),
                $query_extension, $group_extension);
    }

    public function select_Columns_Where($columns, $where_conditions = [],
            $group_extension = '') {
        $select = $this->getQuery_Select($columns);

        $query_extension = '';
        $where = $this->getQuery_Conditions($where_conditions);
        if ($where !== '')
            $query_extension = 'WHERE ' . $where;

        $rows = $this->select_Raw($select, $this->getQuery_From(), $query_extension,
                $group_extension);
        
        return $this->parseRows($rows, $columns);
    }

    public function select_Custom($select, $from, $query_extension = '',
            $group_extension = '') {
        $rows = $this->select_Raw($select, $from, $query_extension,
                $group_extension);

        return $this->parseRows($rows, null);
    }

    public function select_Raw($select, $from, $query_extension,
            $group_extension) {
        $query = 'SELECT ' . $select .
                ' FROM ' . $from;

        if ($query_extension !== '')
            $query .= ' ' . $query_extension;

        if ($this->groupBy !== null)
            $query .= ' GROUP BY ' . $this->groupBy;

        if ($group_extension !== '')
            $query .= ' ' . $group_extension;

        return $this->db->query_Select($query);
    }

    public function select_Where($conditions = [], $group_extension = '',
            $tableOnly = false) {
        $where = '';

        $conditions = $this->getQuery_Conditions($conditions, $tableOnly);
        if ($conditions !== '')
            $where .= 'WHERE ' . $conditions;

        return $this->select($where, $group_extension);
    }

    public function setColumnParser($columnName, array $parser) {
        $this->clearColumnParsers($columnName);
        $this->addColumnParser($columnName, $parser);
    }

    public function setColumns($columns) {
        $column_infos = [];
        foreach ($columns as $columnName => $column) {
            $column_infos[$columnName] = [
                $this->prefix . $this->db->quote($columnName),
                $column,
            ];
        }

        $this->addColumns($column_infos);
    }

    public function setColumns_Optional(array $columnNames) {
        foreach ($columnNames as $columnName) { 
            $column = $this->getColumnRef($columnName);
            $selectColumnNames_Index = array_search($columnName, 
                    $this->selectColumnNames);

            if ($selectColumnNames_Index !== false) {
                array_splice($this->selectColumnNames, $selectColumnNames_Index, 1);
            }
        }
    }

    public function setColumns_Ref(TTable $table, $refColumnNames) {
        $refColumnInfos = [];
        foreach ($refColumnNames as $columnName => $ref_column_name) {
            $refColumnInfos[$columnName] = [ $this->prefix . $this->db->quote($columnName),
                    $ref_column_name];
        }
        $this->addColumns_Ref($table, $refColumnInfos, false);
    }

    public function setGroupBy($group_by) {
        $this->groupBy = $group_by;
    }

    public function setJoin($join) {
        $this->join = $join;
    }

    public function setPKs(array $primaryKeys) {
        $this->primaryKeys = $primaryKeys;
    }

    public function setSelectColumnNames($select_column_names) {
        $this->selectColumnNames = $select_column_names;
    }

    // public function setValidator(VField $Validator_field)
    // {
    //     if ($validator === null) {
    //         if (array_key_exists(0, $this->validators))
    //             unset($this->validators[0]);
    //
    //         return;
    //     }
    //
    //     $this->validators[0] = $Validator_field;
    // }
    //
    // public function setValidatorInfo($columnName, $Validator_info)
    // {
    //     $this->validators[0] = $this->getColumn($columnName)['field']
    //             ->getVField($Validator_info);
    // }

    public function setColumnVFields($columnName, $default_v_field_info,
            $vFields = []) {
        $column = &$this->getColumnRef($columnName);
        $column['vFields'] = [];
        if ($default_v_field_info !== null) {
            try {
                $column['vFields'][] = $column['field']->getVField(
                        $default_v_field_info);
            } catch (\Exception $e) {
                throw new \Exception("Cannot set {$columnName} VFields -> " . 
                        $e->getMessage());
            }
        }

        $this->addColumnVFields($columnName, $vFields);
    }

    public function stripRow($row, $table_columns_only = true) {
        foreach ($row as $columnName => $column_value) {
            if (!$this->columnExists($columnName, $table_columns_only))
                unset($row[$columnName]);
        }

        return $row;
    }

    public function update(array $rows, bool $ignoreNotExistingColumns = false) {
        $this->checkColumns();

        if (count($rows) === 0)
            return true;

        $rows = array_values($rows);

        if (!is_array($rows[0]))
            throw new \Exception('Expecting `rows` to be array of arrays.');

        $pks = $this->getPKs();
        foreach ($pks as $pk) {
            if (!array_key_exists($pk, $rows[0]))
                throw new \Exception("Primary Key '{$pk}' does not exist in row.");
        }

        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $this->db->transaction_Start();
            $localTransaction = true;
        }

        $columns = [];
        foreach ($rows[0] as $columnName => $columnValue) {
            if (!$ignoreNotExistingColumns) {
                $columns[$columnName] = $this->getColumn($columnName, true);
            } else {
                if ($this->columnExists($columnName, true))
                    $columns[$columnName] = $this->getColumn($columnName, true);
            }
        }

        $rows_WithNullPKs = [];
        $rows_WithPKs = [];

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (!is_array($row))
                throw new \Exception('Expecting `rows` to be array of arrays.');

            $row_Filtered = [];

            foreach ($columns as $columnName => $column) {
                if (!array_key_exists($columnName, $row)) {
                    throw new \Exception('Inconsistent/unknown column ' .
                            "`{$columnName}` in rows.");
                }

                $row_Filtered[$columnName] = $row[$columnName];

                // $row_DB[$columnName] = $this->escapeColumnValue($row, $column, 
                //         $columnValue);

                // if ($columnValue instanceof CRawValue) {
                //     foreach ($columns[$columnName]['parsers'] as $column_parser) {
                //         if ($column_parser['in'] !== null) {
                //             $columnValue = $column_parser['in']($row, $columnName, $columnValue);
                //         }
                //     }

                //     $row_DB[] = $columns[$columnName]['field']->escape($this->db, $columnValue);
                // } else 
                //     $row_DB[] = $columnValue->getValue();
            }

            $row = $row_Filtered;

            if (count($columns) !== count($row)) {
                throw new \Exception("Wrong columns number in row '{$i}'" .
                        "(inconsistency with first row).");
            }

            $isNew = true;
            foreach ($pks as $pk) {
                if ($row[$pk] !== null) {
                    $isNew = false;
                    break;
                }
            }

            if ($isNew)
                $rows_WithNullPKs[] = $row;
            else
                $rows_WithPKs[] = $row;

            // $rows_DB[] = $row_DB;
            // $valuesArr_DB[] = '(' . implode(',', $row_DB) . ')';
        }

        $rows_PKs_ToCheck = [];
        foreach ($rows_WithPKs as $row) {
            $row_PKs = [];
            foreach ($pks as $pk)
                $row_PKs[] = $row[$pk];
            $rows_PKs_ToCheck[] = $row_PKs;
        }

        $rows_Insert = [];
        $rows_Update = [];

        $rows_Existing = $this->select_ByPKs($rows_PKs_ToCheck, 'FOR UPDATE');
        foreach ($rows_WithPKs as $row_WithPKs) {
            $match = false;
            foreach ($rows_Existing as $row_Existing) {
                $match = true;
                foreach ($pks as $pk) {
                    if ($columns[$pk]['field']->parse($row_WithPKs[$pk]) !== 
                            $row_Existing[$pk]) {
                        $match = false;
                        break;
                    }
                }

                if ($match)
                    break;
            }

            if ($match)
                $rows_Update[] = $row_WithPKs;
            else
                $rows_Insert[] = $row_WithPKs;
        }

        foreach ($rows_WithNullPKs as $row_WithNullPKs)
            $rows_Insert[] = $row_WithNullPKs;

        // echo "Insert";
        // print_r($rows_Insert);
        // echo "Update";
        // print_r($rows_Update);

        $tableName_DB = $this->db->quote($this->name);

        /* Update */
        if (count($rows_Update) > 0 && (count($rows[0]) > count($pks))) {
            $update_ColumnQueries_Arr = [];
            foreach ($columns as $columnName => $column) {
                if (in_array($columnName, $pks))
                    continue;

                $columnName_DB = $this->db->quote($columnName);
                $update_ColumnQuery = "{$columnName_DB}=(CASE";
                foreach ($rows_Update as $row) {
                    $update_ColumnQuery .= " WHEN ";
                    $pks_Match_Arr = [];
                    foreach ($pks as $pk) {
                        $pks_Match_Arr[] = $this->db->quote($pk) . '=' . 
                                $this->escapeColumn($row, $columns[$pk],
                                $row[$pk]);
                    }
                    $update_ColumnQuery .= '(' . implode(' AND ', $pks_Match_Arr) . ')';
                    $update_ColumnQuery .= ' THEN ' .  $this->escapeColumn($row, $column, 
                            $row[$columnName]);
                }
                $update_ColumnQuery .= ' END)';
                $update_ColumnQueries_Arr[] = $update_ColumnQuery;
            }

            $update_Where_Arr = [];
            foreach ($rows_Update as $row) {
                $pks_Match_Arr = [];
                foreach ($pks as $pk) {
                    $pks_Match_Arr[] = $this->db->quote($pk) . '=' . 
                                $this->escapeColumn($row, $columns[$pk],
                                $row[$pk]);
                }
                $update_Where_Arr[] = '(' . implode(' AND ', $pks_Match_Arr) . ')';
            }

            $update_Query = "UPDATE {$tableName_DB} SET " . implode(',', 
                    $update_ColumnQueries_Arr) . " WHERE " . implode(' OR ', 
                    $update_Where_Arr);

            // echo $update_Query;

            if (!$this->db->query_Execute($update_Query))
                throw new \Exception("Cannot update rows.");
        }
        
        /* Insert */
        $rowsInserted = 0;
        // if (count($rows_Insert) >= 2) {
        //     $rows_Insert[100] = $rows_Insert[0];
        // }
        while (count($rows_Insert) > $rowsInserted) {
            $rows_Insert_Part = array_slice($rows_Insert, $rowsInserted, 
                    min(count($rows_Insert) - $rowsInserted, 
                    EC\MDatabase::$MaxInsertRows));

            $valuesArr_DB = [];
            foreach ($rows_Insert_Part as $row) {
                $row_DB = [];
                foreach ($columns as $columnName => $column) {
                    $row_DB[$columnName] = $this->escapeColumn($row, $column, 
                            $row[$columnName]);
                }

                $valuesArr_DB[] = '(' . implode(',', $row_DB) . ')';
            }

            /* Column Names */
            $columnNames_DB = [];
            foreach ($columns as $columnName => $col)
                $columnNames_DB[] = $this->db->quote($columnName);
            $columnNames_DB_str = implode(',', $columnNames_DB);

            /* Values */
            $db_values = implode(',', $valuesArr_DB);

            $insert_Query = "INSERT INTO {$tableName_DB} ({$columnNames_DB_str})" .
                    " VALUES {$db_values}";

            // echo $insert_Query;

            if (!$this->db->query_Execute($insert_Query))
                throw new \Exception("Cannot insert rows.");

            $affectedRowsCount = $this->db->getAffectedRows();
            $rows_Insert_Count = count($rows_Insert_Part);
            if ($affectedRowsCount !== $rows_Insert_Count) {
                // print_r($insert_Query);
                // echo "#";
                // print_r($this->db->getAffectedRows());
                // echo "#";
                print_r($rowsInserted);

                throw new \Exception("Cannot insert all rows." .
                        " Affected: {$affectedRowsCount}." .
                        " To Insert: {$rows_Insert_Count}.");
            }

            $rowsInserted += $rows_Insert_Count;

            $this->lastInsertedId = $this->db->getLastInsertedId();
        }

        if ($rowsInserted !== count($rows_Insert))
            throw new \Exception("'rowsInserted' does not equal 'rows_Insert'.");

        if ($localTransaction) {
            if (!$this->db->transaction_Finish(true))
                throw new \Exception('Cannot autocommit.');
        }

        return true;
    }

    public function update_ByColumns($rows, $whenColumns) {
        $existing_Conditions = [];
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $existing_Condition = [];
            foreach ($whenColumns as $whenColumn) {
                if (!array_key_exists($whenColumn, $row))
                    throw new \Exception("'whenColumn' in row '{$i}' does not exist.");

                $existing_Condition[] = [ $whenColumn, '=', $row[$whenColumn] ];
            }

            $existing_Conditions[] = [ 'OR', $existing_Condition ];
        }

        $existing_Rows = $this->select_Where($existing_Conditions);

        $update_Rows = [];
        $left_Rows = [];
        foreach ($rows as $row) {
            $rowMatch = false;
            foreach ($existing_Rows as $existing_Row) {
                $whenMatch = true;
                foreach ($whenColumns as $whenColumn) {
                    if ($row[$whenColumn] !== $existing_Row[$whenColumn]) {
                        $whenMatch = false;
                        break;
                    }
                }

                if ($whenMatch === true) {
                    $row['_Id'] = $existing_Row['_Id'];
                    $update_Rows[] = $row;

                    $rowMatch = true;
                }
            }

            if (!$rowMatch)
                $update_Rows[] = $row;
        }

        return $this->update($update_Rows);
    }

    public function update_Where($values, $where_conditions = []) {
        $this->checkColumns();

        $db_sets = [];
        foreach ($values as $columnName => $value) {
            $column = $this->getColumn($columnName, true);
            $db_sets[] = $this->alias . '.' . $column['name'] . '=' .
                    $this->escapeColumn($values, $column, $value);
        }

        $where = $this->getQuery_Conditions($where_conditions, false);

        $query = 'UPDATE ' . $this->getQuery_From() . ' SET ' . 
                implode(',', $db_sets);

        if ($where !== '')
            $query .= ' WHERE ' . $where;

        return $this->db->query_Execute($query);
    }

    public function validate(EC\Forms\CValidator $validator, $fieldInfos) {
        foreach ($fieldInfos as $field_name => $field_info) {
            $validator->add($field_name, $field_info[1],
                    $this->getColumn($field_info[0])['vFields']);
        }
    }

    public function validateDefault(EC\Forms\CValidator $validator, $values,
            $ignoreColumns = []) {
        $fieldInfos = [];
        foreach ($values as $columnName => $value) {
            if (in_array($columnName, $ignoreColumns))
                continue;
                
            $fieldInfos[$columnName] = [ $columnName, $value ];
        }

        return $this->validate($validator, $fieldInfos);
    }

    public function validateDefault_All(EC\Forms\CValidator $validator, $values,
            $ignoreColumns = []) {
        $fieldInfos = [];
        $columnNames = $this->getColumnNames(true);
        foreach ($columnNames as $columnName) {
            if (in_array($columnName, $ignoreColumns))
                continue;

            $value = array_key_exists($columnName, $values) ? 
                    $values[$columnName] : null;
                
            $fieldInfos[$columnName] = [ $columnName, $value ];
        }

        return $this->validate($validator, $fieldInfos);
    }

    // public function validateRow($row) {
    
    // }


    private function escapeArray(FField $column, $values) {
        $db_values = [];
        foreach ($values as $value)
            $db_values[] = $column->escape($this->db, $value);

        return '(' . implode(',', $db_values) . ')';
    }

    private function escapeColumn(array $row, array $column, $value) {
        if ($value instanceof CRawValue)
            return $value->getValue();

        foreach ($column['parsers'] as $column_parser) {
            if ($column_parser['in'] !== null)
                $value = $column_parser['in']($row, $column['name'], $value);
        }

        return $column['field']->escape($this->db, $value);
    }

    private function getQuery_Conditions_Helper($column_values, $logic_operator,
            $tableOnly = false) {
        if (!is_array($column_values))
            throw new \Exception('`column_values` must be an array.');

        $args = [];
        if ($logic_operator === 'NOT') {
            return 'NOT (' . $this->getQuery_Conditions_Helper($column_values, 
                    'AND', $tableOnly) . ')';
        }

        if (count($column_values) === 0)
            return '';

        if (count($column_values) === 2) {
            if ($column_values[0] === 'AND' || $column_values[0] === 'OR' ||
                    $column_values[0] === 'NOT') {
                // print_r($column_values[1]);
                return $this->getQuery_Conditions_Helper($column_values[1], 
                        $column_values[0], $tableOnly);
            }
        }

        foreach ($column_values as $key => $column_condition) {
            if (!is_array($column_condition)) {
                // echo "Test: \r\n";
                // print_r($column_values);
                throw new \Exception('`column_condition` must be an array');
            }

            if (count($column_condition) === 0) 
                continue;
            else if ($key === 'OR' || $key === 'AND') {
                $args[] = '(' . $this->getQuery_Conditions_Helper($column_condition,
                        $key, $tableOnly) . ')';
                continue;
            }  else if (count($column_condition) === 1) {
                $t_logic_operator = array_keys($column_condition)[0];
                if ($t_logic_operator !== 'OR' && $t_logic_operator !== 'AND') {
                    $args[] = '(' . $this->getQuery_Conditions_Helper(
                            [ 'AND', $column_condition ], 'AND', $tableOnly) . ')';
                    continue;
                }

                if (count($column_condition[$t_logic_operator]) === 0)
                    continue;

                $args[] = '(' . $this->getQuery_Conditions_Helper(
                        $column_condition[$t_logic_operator], $t_logic_operator,
                        $tableOnly) . ')';
                continue;
            } else if (!is_int($key))
                throw new \Exception("Unknown logic operator `{$key}`.");

            if (count($column_condition) === 1) {
                if (is_array($column_condition[0])) {
                    $args[] = '(' . $this->getQuery_Conditions_Helper(
                        'AND', $column_condition[0], 
                        $tableOnly) . ')';
                    continue;
                }
            }

            if (count($column_condition) === 2) {
                if ($column_condition[0] === 'OR' || $column_condition[0] === 'AND' 
                        || $column_condition[0] === 'NOT') {
                    $args[] = '(' . $this->getQuery_Conditions_Helper(
                            $column_condition[1], $column_condition[0], 
                            $tableOnly) . ')';
                    continue;
                }
            }

            if (count($column_condition) !== 3) {
                throw new \Exception('`column_condition` must have exactly' .
                        ' 3 positions: ' . print_r($column_condition, true));
            }

            list($columnName, $sign, $value) = $column_condition;

            $column = $this->getColumn($columnName);

            $db_column_name = $tableOnly ? $this->db->quote($columnName) :
                    $this->getColumn($columnName)['expr'];

            $prefix = '';
            if ($sign === null) {
                $db_value = $value;
                $sign = '';
            } else {
                if ($value === null) {
                    if ($sign === '=')
                        $db_value = 'IS NULL';
                    else if ($sign === '<>')
                        $db_value = 'IS NOT NULL';
                    else
                        throw new \Exception("Unknown `{$sign}` and `null` conjuction.");

                    $sign = '';
                } else {
                    if (is_array($value)) {
                        if (count($value) === 0) {
                            if ($sign === 'IN') {
                                $args[] = 'TRUE = FALSE';
                                continue;
                            } else if ($sign === 'NOT IN') {
                                $args[] = 'TRUE = TRUE';
                                continue;
                            }
                        } else
                            $db_value = ' ' . $this->escapeArray($column['field'], $value);
                    } else {
                        if (is_string($value) && $sign === '==') {
                            $prefix = 'BINARY ';
                            $sign = '=';
                        }

                        $db_value = ' ' . $column['field']->escape($this->db, $value);
                    }
                }
            }

            $args[] = "{$prefix}{$db_column_name} {$sign}{$db_value}";
        }

        return implode(" {$logic_operator} ", $args);
    }

    private function &parseColumnInfo($columnName, $column_info, $extra, $optional) {
        $column_optional = false;

        if (!is_array($column_info)) {
            $column_expr = $this->prefix . $columnName;
            $columnField = $column_info;
        } else {
            $column_expr = $column_info[0];
            $columnField = $column_info[1];
        }

        if ($extra && $column_expr !== null)
            $column_expr = '(' . $column_expr . ')';

        $vField = $columnField === null ? null : $columnField->getVField();
        $column = [
            'name' => $columnName,
            'optional' => $optional,
            'expr' => $column_expr,
            'field' => $columnField,

            'parsers' => [],
            'vFields' => [ $vField ]
        ];

        return $column;
    }

}
