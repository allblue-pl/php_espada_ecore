<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HTablesIds {

    static public function GetNext(EC\MDatabase $db, EC\Database\TTable $table) {
        $db->requireTransaction();

        $next_id = null;

        /* Auto Increment */
        $query = 'SELECT AUTO_INCREMENT' .
                ' FROM  INFORMATION_SCHEMA.TABLES' .
                ' WHERE TABLE_SCHEMA = DATABASE()' .
                ' AND TABLE_NAME = ' . $db->escapeString($table->getTableName()) .
                ' FOR UPDATE';
        $rows = $db->query_Select($query);
        if (count($rows) > 0)
            $next_id = (int)$rows[0]['AUTO_INCREMENT'];

        /* Table */
        // $max_id_row = $table->row_Columns([ 'id' ], '', 'ORDER BY id DESC', true);
        // if ($max_id_row !== null) {
        //     if ($max_id_row['id'] > $next_id)
        //         $next_id = $max_id_row['id'];
        // }

        /* App */
        // $max_id_row = (new TTableIds($this->db))->row_Where([
        //     [ 'tableName', '=', $table->getTableName() ]
        // ], '', true);
        // if ($max_id_row !== null) {
        //     if ($max_id_row['maxId'] > $next_id)
        //         $next_id = $max_id_row['maxId'];
        // }

        return $next_id;
    }

    static public function GetNexts(EC\MDatabase $db, array $tables) {
        $db->requireTransaction();

        $next_id = 0;

        $db_table_names = [];
        foreach ($tables as $table)
            $table_names[] = $db->escapeString($table->getTableName());
        $db_table_names_str = implode(',', $db_table_names);

        /* Auto Increment */
        $query = 'SELECT TABLE_NAME, AUTO_INCREMENT' .
                ' FROM  INFORMATION_SCHEMA.TABLES' .
                ' WHERE TABLE_SCHEMA = DATABASE()' .
                " AND TABLE_NAME IN ({$db_table_names_str})" .
                ' FOR UPDATE';
        $rows = $db->query_Select($query);

        $nexts = [];
        foreach ($tables as $table)
            $nexts[$table->getTableName()] = null;
        foreach ($rows as $row) {
            if (!array_key_exists($row['TABLE_NAME'], $nexts))
                throw new Error('Unknown table name.');

            $nexts[$row['TABLE_NAME']] = (int)$row['AUTO_INCREMENT'];
        }

        return $nexts;
    }

    static public function ParseAliases($tables, $tables_ids) {
        $ids = [];
        foreach ($tables as $table_alias => $table) {
            /* To work with `TableIds_UpdateInfos` $tables format. */
            if (is_array($table))
                $table = $table[0];

            $table_name = $table->getTableName();

            if (array_key_exists($table_name, $tables_ids))
                $ids[$table_alias] = $tables_ids[$table_name];
            else
                $ids[$table_alias] = [];
        }

        return $ids;
    }

}
