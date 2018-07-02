<?php namespace EC\SPKTables;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HSPKTables
{

    static private $Initialized = false;

    static public function Create(EC\MSPK $abf, $table_name, $table_info)
    {
        self::Init($abf);

        $table = self::ParseInfo($table_info);
        $table_json = json_encode($table);

        $abf->addAppScript(
            "SPK.\$eTables.add('{$table_name}', {$table_json})"
        );
    }

    static public function Init(EC\MSPK $abf)
    {
        if (self::$Initialized)
            return;
        self::$Initialized = true;

        $abf->addTexts('SPKTables');
    }

    static public function GetQueryExtensions(EC\Database\TTable $t_table,
            $spk_table, $table_args, $where = '')
    {
        $spk_table = self::ParseInfo($spk_table);

        $query_info = self::GetQueryInfo($t_table, $spk_table, $table_args);

        if ($query_info['where'] !== '') {
            if ($where === '')
                $where = $query_info['where'];
            else
                $where .= ' AND ' . $query_info['where'];
        }
        if ($where !== '')
            $where = 'WHERE ' . $where;

        $group_extension = 'ORDER BY ' . $query_info['orderBy'] .
                " LIMIT {$query_info['limit']['offset']}" .
                ", {$query_info['limit']['limit']}";

        return [
            'query' => $where,
            'group' => $group_extension
        ];
    }

    static public function GetQueryInfo(EC\Database\TTable $t_table,
            $spk_table, $table_args)
    {
        $spk_table = self::ParseInfo($spk_table);

        return [
            'where' => self::GetQueryInfo_Filter($t_table, $spk_table, $table_args),
            'orderBy' => self::GetQueryInfo_OrderBy($t_table, $spk_table, $table_args),
            'limit' => self::GetQueryInfo_Limit($t_table, $spk_table, $table_args)
        ];
    }

    // static public function GetQuery(EC\MDatabase $database, $select,
    //         $where, $filter_columns, $order_columns, $table_fields)
    // {
    //     /* Select */
    //     $query = $select;
    //
    //     /* Where */
    //     if ($where !== '')
    //         $query .= " WHERE ({$where})";
    //
    //     /* Filter */
    //     $filter = self::GetQuery_Filter($database, $filter_columns,
    //             $table_fields);
    //     if ($filter !== null) {
    //         if ($where === '')
    //             $query .= ' WHERE';
    //         else
    //             $query .= ' AND';
    //
    //         $query .= " ({$filter})";
    //     }
    //
    //     /* OrderBy */
    //     $query .= self::GetQuery_OrderBy($order_columns, $table_fields);
    //
    //     /* Limit */
    //     $query .= self::GetQuery_Limit($table_fields);
    //
    //     return $query;
    // }

    static public function ParseInfo($table_info)
    {
        $table = [
            'columns' => [],
            'apiUri' => null,
            'image' => '',
            'orderBy' => null,
            'hiddenColumnNames' => []
        ];

        foreach ($table_info as $prop_name => $prop) {
            if (!array_key_exists($prop_name, $table))
                throw new \Exception("Property `{$prop_name}` doesn't exist.");
        }

        /* Columns */
        if (!array_key_exists('columns', $table_info))
            throw new \Exception('`columns` not set.');

        foreach ($table_info['columns'] as $column_info) {
            $column = [
                'name' => '',
                'header' => '',
                'orderBy' => null,
                'filter' => true
            ];

            foreach ($column_info as $prop_name => $prop) {
                if (!array_key_exists($prop_name, $column))
                    throw new \Exception("Property `{$prop_name}` doesn't exist.");

                $column[$prop_name] = $prop;
            }

            $table['columns'][$column['name']] = $column;
        }

        /* Api Uri */
        if (!array_key_exists('apiUri', $table_info))
            throw new \Exception('`apiUri` not set.');
        $table['apiUri'] = $table_info['apiUri'];

        /* Image */
        if (array_key_exists('image', $table_info))
            $table['image'] = $table_info['image'];

        /* Order By */
        if (array_key_exists('orderBy', $table_info))
            $table['orderBy'] = $table_info['orderBy'];
        else
            $table['orderBy'] = [ array_keys($table['columns'])[0], false ];

        if (array_key_exists('hiddenColumnNames', $table_info))
            $table['hiddenColumnNames'] = $table_info['hiddenColumnNames'];

        return $table;
    }

    static public function ParseRows($table_info, $rows)
    {
        $spk_table = self::ParseInfo($table_info);

        $parsed_rows = [];
        foreach ($rows as $row) {
            $parsed_row = [];
            foreach ($spk_table['columns'] as $col_name => $header) {
                if ($col_name[0] === '$') {
                    $parsed_row[] = '';
                    continue;
                }

                if (!array_key_exists($col_name, $row)) {
                    throw new \Exception("Column `{$col_name}`" .
                            " doesn't exist in a table.");
                }

                $parsed_row[] = $row[$col_name];
            }

            $parsed_rows[] = $parsed_row;
        }

        return $parsed_rows;
    }

    static public function Select(EC\Database\TTable $t_table,
            $spk_table_info, $table_args, $where = '')
    {
        $spk_table = self::ParseInfo($spk_table_info);

        $query_extensions = EC\HSPKTables::GetQueryExtensions(
                $t_table, $spk_table, $table_args, $where);

        $rows = $t_table->select($query_extensions['query'],
                $query_extensions['group']);

        return HSPKTables::ParseRows($spk_table, $rows);
    }

    static private function GetQueryInfo_Filter(EC\Database\TTable $t_table,
            $spk_table, $table_args)
    {
        $spk_table = self::ParseInfo($spk_table);

        if (!isset($table_args['filter']))
            return '';

        $filter = $table_args['filter'];
        $query = '';

        if ($filter !== '') {
            $conditions = [];
            foreach ($spk_table['columns'] as $column_name => $column) {
                if (!$column['filter'])
                    continue;

                $db_column_name = $t_table->getColumn($column_name)['expr'];
                if ($db_column_name === null)
                    continue;

                $filter = EC\HStrings::EscapeLangCharacters($filter);
                $db_filter = $t_table->getDB()->escapeString("%{$filter}%");

                $conditions[] = "CAST({$db_column_name} AS CHAR)" .
                        " LIKE {$db_filter}";
            }

            $query = '(' . implode(' OR ', $conditions) . ')';
        }

        return $query;
    }

    static private function GetQueryInfo_OrderBy(EC\Database\TTable $t_table,
            $spk_table, $table_args)
    {
        $spk_table = self::ParseInfo($spk_table);

        $first_order_column_name = null;
        if (isset($table_args['orderColumnName'])) {
            if (array_key_exists($table_args['orderColumnName'],
                    $spk_table['columns']))
                $first_order_column_name = $table_args['orderColumnName'];
        }

        if (isset($table_args['orderColumnDesc']))
            $order_type = (bool)$table_args['orderColumnDesc'];
        else
            $order_type = false;

        $query = '';
        $first = true;

        $order_columns = [];
        $i = -1;
        foreach ($spk_table['columns'] as $column_name => $column) {
            if ($column_name === $first_order_column_name)
                continue;

            if ($column['orderBy'] !== null)
                $order_columns[$column_name] = $column;
        }

        uasort($order_columns, function($col_a, $col_b) {
            return $col_b['orderBy'][0] - $col_a['orderBy'][0];
        });

        if ($first_order_column_name === null)
            $first_order_column_name = array_keys($spk_table['columns'])[0];

        $table_column = $t_table->getColumn($first_order_column_name);
        $first_order_column_expr = $table_column['expr'];

        $order_by = [ $first_order_column_expr . ($order_type ? ' DESC' : '') ];
        foreach ($order_columns as $column_name => $column) {
            $table_column = $t_table->getColumn($column_name);
            $order_by[] = $table_column['expr'] . ($column['orderBy'][1] ? ' DESC' : '');
        }

        return implode(',', $order_by);
    }

    static private function GetQueryInfo_Limit(EC\Database\TTable $t_table,
            $spk_table, $table_args)
    {
        $spk_table = self::ParseInfo($spk_table);

        $offset = '0';
        $limit = '18446744073709551615';

        if (isset($table_args['offset']))
            $offset = intval($table_args['offset']);
        if (isset($table_args['limit']))
            $limit = intval($table_args['limit']);

        return  [
            'offset' => $offset,
            'limit' => $limit
        ];
    }

}
