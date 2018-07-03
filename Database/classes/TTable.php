<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class TTable
{

    private $db = null;

    private $name = null;
    private $alias = '';
    private $prefix = '';

    private $join = null;
    private $groupBy = null;

    private $columns = [];
    private $columns_Table = [];

    private $selectColumnNames = null;

    private $rowParsers = [];

    public function __construct(EC\MDatabase $db, $table_name,
            $table_alias = null)
    {
        $this->db = $db;

        $this->name = $table_name;
        $this->alias = $table_alias;
        if ($table_alias !== null)
            $this->prefix = $table_alias . '.';

        $this->addColumns_Optional([
            'Count' => [ 'COUNT(*)', new FInt(true, 11) ]
        ]);
    }

    public function addColumnVFields($columnName, $v_fields)
    {
        $column = &$this->getColumnRef($columnName);
        foreach ($v_fields as $v_field)
            $column['vFields'][] = $v_field;
    }

    public function addColumns($column_infos, $extra = false, $optional = false)
    {
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

    public function addColumns_Extra($columns)
    {
        $this->addColumns($columns, true);
    }

    public function addColumns_Ref(TTable $table, $ref_column_infos, $extra = true)
    {
        foreach ($ref_column_infos as $columnName => $ref_column_info) {
            if ($this->columnExists($columnName))
                continue;

            $column_expr = $ref_column_info[0];
            $ref_column = $table->getColumn($ref_column_info[1]);

            $column_info = [ $column_expr, $ref_column['field'] ];
            $this->addColumns([ $columnName => $column_info ], $extra);

            $column = &$this->getColumnRef($columnName);

            $column['parser'] = $ref_column['parser'];
            $column['vFields'] = $ref_column['vFields'];
        }
    }

    public function addColumns_Ref_All(TTable $table, $fieldPrefix = '',
            $excludedColumns = [ 'Id' ], $includedColumns = null)
    {
        $this->addColumns_Ref($table, $table->getColumnTableRefs( 
                $fieldPrefix, $excludedColumns, $includedColumns));
    }

    public function addColumns_Optional($columns, $extra = true)
    {
        $this->addColumns($columns, $extra, true);
    }

    public function addRowParser($parser)
    {
        $this->rowParsers[] = $parser;
    }

    public function checkColumns()
    {
        if (count($this->columns_Table) > 0)
            return;

        $class_name = get_called_class();

        throw new \Exception("Table columns not set in `{$class_name}.");
    }

    public function columnExists($columnName, $is_table_column = false)
    {
        if ($is_table_column)
            return array_key_exists($columnName, $this->columns_Table);
        else
            return array_key_exists($columnName, $this->columns);
    }

    public function count($group_extension = '')
    {
        $query = "SELECT COUNT(DISTINCT {$this->prefix}id) as count" .
                ' FROM ' . $this->getQuery_From();

        if ($group_extension !== '')
            $query .= ' ' . $group_extension;
        $query .= ' LIMIT 1';

        $rows = $this->db->query_Select($query);

        if (count($rows) === 0)
            return null;

        return $this->db->unescapeInt($rows[0]['count']);
    }

    public function count_Where($where_conditions, $group_extension = '')
    {
        $where = $this->getQuery_Conditions($where_conditions);
        if ($where !== '')
            $group_extension = 'WHERE ' . $where . ' ' . $group_extension;

        return $this->count($group_extension);
    }

    public function delete($query_extension = '')
    {
        $query = 'DELETE ' . $this->db->quote($this->alias) . ' FROM ' .
                $this->getQuery_From();

        if ($query_extension !== '')
            $query .= ' ' . $query_extension;

        return $this->db->query_Execute($query);
    }

    public function delete_ById($id)
    {
        return $this->delete_Where([
            [ 'Id', '=', $id ]
        ]);
    }

    public function delete_Where($conditions)
    {
        return $this->delete(
            'WHERE ' . $this->getQuery_Conditions($conditions)
        );
    }

    public function escapeColumn($columnName, $value)
    {
        return $this->getColumn($columnName)['field']->escape($this->db, $value);
    }

    public function getColumn($columnName, $only_table = false)
    {
        return $this->getColumnRef($columnName, $only_table);
    }

    public function &getColumnRef($columnName, $only_table = false)
    {
        // if ($columnName === 'Cow_Nr') {
        //     echo $columnName . '#' . $only_table . "\r\n";
        //     print_r($this->columns_Table);
        //     echo array_key_exists($columnName, $this->columns_Table) . "\r\n";
        // }

        if (!array_key_exists($columnName, $this->columns)) {
            $class_name = get_called_class();
            throw new \Exception("Column `{$columnName}` does not exist" .
                    " in {$class_name}.");
        }

        if ($only_table && !array_key_exists($columnName, $this->columns_Table)) {
            $class_name = get_called_class();
            throw new \Exception("Column `{$columnName}` is not table column" .
                    " in {$class_name}.");
        }

        return $this->columns[$columnName];
    }

    public function getColumnNames($only_table = false, $prefix = null)
    {
        $columnNames = null;

        if ($only_table)
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

    public function getColumnTableRefs($fieldPrefix = '',
            $excludedColumns = [ 'Id' ], $includedColumns = null)
    {
        $columnRefs = [];
        foreach ($this->columns as $columnName => $columnField) {
            $column = $this->getColumn($columnName);
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

    public function getDB()
    {
        return $this->db;
    }

    public function getTableName()
    {
        return $this->name;
    }

    public function getTableName_Quoted()
    {
        return $this->db->quote($this->name);
    }

    public function getQuery_Conditions($column_values, $table_only = false)
    {
        return $this->getQuery_Conditions_Helper($column_values, 'AND',
                $table_only);
    }

    public function getQuery_From()
    {
        $query = $this->getDB()->quote($this->name);
        if ($this->alias !== null)
            $query .= " AS {$this->alias}";

        if ($this->join !== null)
            $query .= ' ' . $this->join;

        return $query;
    }

    public function getQuery_Select($columnNames = null)
    {
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


    public function getQuery_SelectColumn($columnName)
    {
        $column = $this->getColumn($columnName);

        $db_column_name = $this->db->quote($columnName);
        $db_column_expr = $column['expr'];

        if ($db_column_expr === null)
            return null;

        return "{$db_column_expr} AS {$db_column_name}";
    }

    public function insert($rows)
    {
        $this->checkColumns();

        /* Column Names */
        $columnNames = array_keys($this->columns);

        $db_column_names = [];
        foreach ($columnNames as $columnName)
            $db_column_names[] = $this->db->quote($columnName);
        $db_column_names_str = implode(',', $db_column_names);

        /* Values */
        $db_values_array = [];
        foreach ($rows as $row) {
            $db_row = [];
            foreach ($row as $col_name => $col_val) {
                $db_row[] = $this->getColumn($col_name)
                        ->escape($this->db, $col_val);
            }

            $db_values_array[] = '(' . implode(',', $db_row) . ')';
        }
        $db_values = implode(',', $db_values_array);

        /* Update Columns */
        $db_update_columns_array = [];
        foreach ($db_column_names as $db_col_name)
            $db_update_columns_array[] = "{$db_col_name} = VALUES($col_name)";
        $db_update_columns = implode(',', $db_update_columns_array);

        $query = "INSERT INTO {$this->name} ({$db_columns})" .
                " VALUES {$db_values}" .
                " ON DUPLICATE KEY UPDATE {$db_update_columns}";

        return $this->db->query_Execute($query);
    }

    public function parseRow($row)
    {
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

        $parsed_row = [];
        foreach ($row as $columnName => $db_column_value) {
            if (!$this->columnExists($columnName)) {
                $parsed_row[$columnName] = $db_column_value;
                continue;
            }

            $column = $this->getColumn($columnName);

            if (array_key_exists('parser', $column)) {
                if (array_key_exists('out', $column['parser'])) {
                    $parsed_cols = $column['parser']['out']($row, $columnName,
                            $unescaped_row[$columnName]);
                    foreach ($parsed_cols as $parsed_col_name => $parsed_col_value) {
                        if ($parsed_col_name !== $columnName) {
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

                    continue;
                }
            }

            $parsed_row[$columnName] = $unescaped_row[$columnName];
        }

        return $parsed_row;

        // foreach ($this->columns as $col_name =>
        //         list($columnName, $column)) {
        //
        //
        //     if (isset($row[$col_name])) {
        //         $col_value = $column->unescape($this->db, $row[$col_name]);
        //     }
        //         $row[$col_name] = $column->unescape($this->db, $row[$col_name]);
        // }

        // return $this->_parseRow($row);
    }

    public function parseRows($rows)
    {
        $parsed_rows = [];
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $parsed_row = $this->parseRow($row);
            foreach ($this->rowParsers as $row_parser)
                $row_parser($parsed_row, $i);

            $parsed_rows[] = $parsed_row;
        }

        return $parsed_rows;
    }

    public function row($query_extension = '', $group_extension = '',
            $for_update = false)
    {
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

    public function row_ById($id, $group_extension = '', $for_update = false)
    {
        return $this->row_Where([
            [ 'Id', '=', $id ]
        ], $group_extension, $for_update);
    }

    public function row_Columns($columnNames, $query_extension = '',
            $group_extension = '', $for_update = false)
    {
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

    public function row_Columns_Where($columnNames, $where_conditions,
            $group_extension = '', $for_update = false)
    {
        $query_extension = '';

        $where = $this->getQuery_Conditions($where_conditions);
        if ($where !== '')
            $query_extension = 'WHERE ' . $where;

        return $this->row_Columns($columnNames, $query_extension, $group_extension);
    }

    public function row_Where($args = [], $group_extension = '',
            $for_update = false)
    {
        $where = '';

        $conditions = $this->getQuery_Conditions($args);
        if ($conditions !== '')
            $where .= 'WHERE ' . $conditions;

        return $this->row($where, $group_extension, $for_update);
    }

    public function select($query_extension = '', $group_extension = '')
    {
        return $this->select_Columns($this->selectColumnNames, $query_extension,
                $group_extension);
    }

    public function select_Columns($columnNames, $query_extension = '',
            $group_extension = '')
    {
        $select = $this->getQuery_Select($columnNames);

        return $this->select_Custom($select, $this->getQuery_From(),
                $query_extension, $group_extension);
    }

    public function select_Columns_Where($columns, $where_conditions = [],
            $group_extension = '')
    {
        $select = $this->getQuery_Select($columns);

        $query_extension = '';
        $where = $this->getQuery_Conditions($where_conditions);
        if ($where !== '')
            $query_extension = 'WHERE ' . $where;

        return $this->select_Custom($select, $this->getQuery_From(),
                $query_extension, $group_extension);
    }

    public function select_Custom($select, $from, $query_extension = '',
            $group_extension = '')
    {
        $rows = $this->select_Raw($select, $from, $query_extension,
                $group_extension);

        return $this->parseRows($rows);
    }

    public function select_Raw($select, $from, $query_extension,
            $group_extension)
    {
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

    public function select_Where($conditions = [], $group_extension = '')
    {
        $where = '';

        $conditions = $this->getQuery_Conditions($conditions);
        if ($conditions !== '')
            $where .= 'WHERE ' . $conditions;

        return $this->select($where, $group_extension);
    }

    public function setColumnParser($columnName, array $parser)
    {
        foreach ($parser as $parser_type => $parser_info) {
            if ($parser_type !== 'in' && $parser_type !== 'out')
                throw new \Exception('Wrong `parser` format.');
        }

        $column = &$this->getColumnRef($columnName);

        if (array_key_exists('out', $parser))
            $column['parser']['out'] = $parser['out'];

        if (array_key_exists('in', $parser))
            $column['parser']['in'] = $parser['in'];
    }

    public function setColumns($columns)
    {
        $column_infos = [];
        foreach ($columns as $columnName => $column) {
            $column_infos[$columnName] = [
                $this->prefix . $this->db->quote($columnName),
                $column
            ];
        }

        $this->addColumns($column_infos);
    }

    public function setColumns_Ref(TTable $table, $ref_column_names)
    {
        $ref_column_infos = [];
        foreach ($ref_column_names as $columnName => $ref_column_name) {
            $ref_column_infos[$columnName] = [ $this->prefix . $this->db->quote($columnName),
                    $ref_column_name];
        }
        $this->addColumns_Ref($table, $ref_column_infos, false);
    }

    public function setGroupBy($group_by)
    {
        $this->groupBy = $group_by;
    }

    public function setJoin($join)
    {
        $this->join = $join;
    }

    public function setSelectColumns($select_column_names)
    {
        $this->selectColumns = $select_column_names;
    }

    // public function setValidator(VField $validator_field)
    // {
    //     if ($validator === null) {
    //         if (array_key_exists(0, $this->validators))
    //             unset($this->validators[0]);
    //
    //         return;
    //     }
    //
    //     $this->validators[0] = $validator_field;
    // }
    //
    // public function setValidatorInfo($columnName, $validator_info)
    // {
    //     $this->validators[0] = $this->getColumn($columnName)['field']
    //             ->getVField($validator_info);
    // }

    public function setColumnVFields($columnName, $default_v_field_info,
            $v_fields = [])
    {
        $column = &$this->getColumnRef($columnName);
        $column['vFields'] = [];
        if ($default_v_field_info !== null)
            $column['vFields'][] = $column['field']->getVField($default_v_field_info);

        $this->addColumnVFields($columnName, $v_fields);
    }

    public function stripRow($row, $table_columns_only = true)
    {
        foreach ($row as $columnName => $column_value) {
            if (!$this->columnExists($columnName, $table_columns_only))
                unset($row[$columnName]);
        }

        return $row;
    }

    public function update($rows, $ignore_not_existing = false)
    {
        $this->checkColumns();

        if (count($rows) === 0)
            return true;

        $first_key = array_keys($rows)[0];

        if (!is_array($rows[$first_key]))
            throw new \Exception('Expecting `rows` to be array of arrays.');

        $columns = [];
        foreach ($rows[$first_key] as $col_name => $col_val) {
            if (!$ignore_not_existing) {
                $columns[$col_name] = $this->getColumn($col_name, true);
            } else {
                if ($this->columnExists($col_name, true))
                    $columns[$col_name] = $this->getColumn($col_name, true);
            }
        }

        $db_values_array = [];
        foreach ($rows as $row) {
            $db_row = [];

            foreach ($row as $columnName => $column_value) {
                if (!$this->columnExists($columnName, true))
                    unset($row[$columnName]);
            }

            if (count($columns) !== count($row)) {
                throw new \Exception('Wrong columns number ' .
                        '(inconsistency with first row).');
            }

            foreach ($row as $col_name => $col_val) {
                if (!array_key_exists($col_name, $columns))
                    throw new \Exception('Inconsistent/unknown column ' .
                            "`{$col_name}` in rows.");

                if (array_key_exists('in', $columns[$col_name]['parser'])) {
                    $col_val = $columns[$col_name]['parser']['in']($row, $col_name,
                            $col_val);
                }

                $db_row[] = $columns[$col_name]['field']->escape($this->db, $col_val);
            }

            $db_values_array[] = '(' . implode(',', $db_row) . ')';
        }

        /* Column Names */
        $db_column_names = [];
        foreach ($columns as $col_name => $col)
            $db_column_names[] = $this->db->quote($col_name);
        $db_column_names_str = implode(',', $db_column_names);

        /* Values */
        $db_values = implode(',', $db_values_array);

        /* Update Columns */
        $db_update_columns_array = [];
        foreach ($db_column_names as $db_col_name)
            $db_update_columns_array[] = "{$db_col_name} = VALUES($db_col_name)";
        $db_update_columns = implode(',', $db_update_columns_array);

        $db_table_name = $this->db->quote($this->name);

        $query = "INSERT INTO {$db_table_name} ({$db_column_names_str})" .
                " VALUES {$db_values}" .
                " ON DUPLICATE KEY UPDATE {$db_update_columns}";

        return $this->db->query_Execute($query);
    }

    public function update_Where($values, $where_conditions = [])
    {
        $this->checkColumns();

        $db_sets = [];
        foreach ($values as $columnName => $value) {
            $column = $this->getColumn($columnName, true);
            $db_sets[] = $column['name'] . '=' .
                    $this->escapeColumnValue($values, $column, $value);
        }

        $where = $this->getQuery_Conditions($where_conditions, false);

        $query = 'UPDATE ' . $this->getQuery_From() .
                ' SET ' . implode(',', $db_sets);

        if ($where !== '')
            $query .= ' WHERE ' . $where;

        return $this->db->query_Execute($query);
    }

    public function validate(EC\Forms\CValidator $validator, $field_infos)
    {
        foreach ($field_infos as $field_name => $field_info) {
            $validator->add($field_name, $field_info[1],
                    $this->getColumn($field_info[0])['vFields']);
        }
    }

    public function validateDefault(EC\Forms\CValidator $validator, $values)
    {
        $fieldInfos = [];
        foreach ($values as $columnName => $value)
            $fieldInfos[$columnName] = [ $columnName, $value ];

        return $this->validate($validator, $fieldInfos);
    }

    // public function validateRow($row)
    // {
    //
    // }


    private function escapeArray(FField $column, $values)
    {
        $db_values = [];
        foreach ($values as $value)
            $db_values[] = $column->escape($this->db, $value);

        return '(' . implode(',', $db_values) . ')';
    }

    private function escapeColumnValue(array $row, array $column, $value)
    {
        if (array_key_exists('in', $column['parser']))
            $value = $column['parser']['in']($row, $column['name'], $value);

        return $column['field']->escape($this->db, $value);
    }

    private function getQuery_Conditions_Helper($column_values, $logic_operator,
            $table_only = false)
    {
        if (!is_array($column_values))
            throw new \Exception('`column_values` must be an array.');

        $args = [];
        foreach ($column_values as $key => $column_condition) {
            if (!is_array($column_condition))
                throw new \Exception('`column_condition` must be an array.');

            if ($key === 'OR' || $key === 'AND') {
                $args[] = '(' . $this->getQuery_Conditions_Helper($column_condition,
                        $key, $table_only) . ')';
                continue;
            }  else if (count($column_condition) === 1) {
                $t_logic_operator = array_keys($column_condition)[0];
                if ($t_logic_operator !== 'OR' && $t_logic_operator !== 'AND')
                    throw new \Exception('Unknown logic operator.');

                $args[] = '(' . $this->getQuery_Conditions_Helper(
                        $column_condition[$t_logic_operator], $t_logic_operator,
                        $table_only) . ')';
                continue;
            } else if (!is_int($key))
                throw new \Exception("Unknown logic operator `{$key}`.");

            if (count($column_condition) !== 3) {
                throw new \Exception('`column_condition` must have exactly' .
                        ' 3 positions.');
            }

            list($columnName, $sign, $value) = $column_condition;

            $column = $this->getColumn($columnName);

            $db_column_name = $table_only ? $this->db->quote($columnName) :
                    $this->getColumn($columnName)['expr'];

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
                    if (is_array($value))
                        $db_value = ' ' . $this->escapeArray($column['field'], $value);
                    else
                        $db_value = ' ' . $column['field']->escape($this->db, $value);
                }
            }

            $args[] = "{$db_column_name} {$sign}{$db_value}";
        }

        return implode(" {$logic_operator} ", $args);
    }

    private function &parseColumnInfo($columnName, $column_info, $extra, $optional)
    {
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

        $v_field = $columnField === null ? null : $columnField->getVField();
        $column = [
            'name' => $columnName,
            'optional' => $optional,
            'expr' => $column_expr,
            'field' => $columnField,

            'parser' => [],
            'vFields' => [ $v_field ]
        ];

        return $column;
    }

}
