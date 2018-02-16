<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CTablesIds
{

    private $db = null;
    private $appId = 0;
    private $tablesIds = null;

    public function __construct(EC\MDatabase $db, $app_info_row)
    {
        $this->db = $db;
        $this->appId = $app_info_row['Id'];

        $this->tablesIds = $app_info_row['TablesIds'];
    }

    public function check($table_id_pairs)
    {
        foreach ($table_id_pairs as list($table, $id)) {
            if (!in_array($id, $this->tablesIds[$table->getTableName()]))
                return false;
        }

        return true;
    }

    public function checkId(EC\Database\TTable $table, $id)
    {
        $table_name = $table->getTableName();

        if (!array_key_exists($table_name, $this->tablesIds))
            return false;

        return in_array($id, $this->tablesIds[$table_name]);
    }

    public function update($tables)
    {
        $this->db->requireNoTransaction();

        $t_infos = new TInfos($this->db);

        $tables_ids = null;
        $changed = false;

        $app_info = $t_infos->row_ById($this->appId, '', true);
        if ($app_info === null) {
            $tables_ids = [];
            $changed = true;
        } else
            $tables_ids = $app_info['TablesIds'];

        foreach ($tables as $table_alias => list($table, $table_ids_limit)) {
            $table_name = $table->getTableName();
            if (!array_key_exists($table_name, $tables_ids))
                $tables_ids[$table_name] = [];
            $table_ids = &$tables_ids[$table_name];

            $used_ids_rows = $table->select_Where([
                [ 'Id', 'IN',  array_merge([ -1 ], $table_ids)]
            ]);
            foreach ($used_ids_rows as $used_id_row) {
                unset($table_ids[array_search($used_id_row['Id'], $table_ids)]);
                $changed = true;
            }
            $table_ids = array_values($table_ids);

            if (count($table_ids) >= $table_ids_limit)
                continue;

            $changed = true;

            $this->db->transaction_Start();

            $next_id = HTablesIds::GetNext($this->db, $table);
            $last_id = $next_id + ($table_ids_limit - count($table_ids) - 1);
            for ($i = $next_id; $i <= $last_id; $i++)
                $table_ids[] = $i;

            $query = 'ALTER TABLE ' . $table->getTableName_Quoted() .
                    ' AUTO_INCREMENT = ' . $this->db->escapeInt($last_id + 1);
            if (!$this->db->query_Execute($query))
                return null;
            /* Altering table implicitly commits changes. */
            $this->db->transaction_Finish(true);

            // if (!(new TTableIds($this->db))->update([[
            //     'tableName' => $table_name,
            //     'maxId' => $last_id
            //         ]]))
            //     return null;
        }

        if ($changed) {
            if (!$t_infos->update([[
                'Id' => $this->appId,
                'TablesIds' => $tables_ids
                    ]]))
                return null;
        }

        $this->tablesIds = $tables_ids;

        return HTablesIds::ParseAliases($tables, $this->tablesIds);
    }

    public function get($tables)
    {
        return HTablesIds::ParseAliases($tables, $this->tablesIds);
    }

    private function refresh()
    {
        $this->db->requireTransaction();

        $row = (new TInfos($this->db))->row_ById($this->appId);

        if ($row === null)
            return [];

        $this->tablesIds = $row['TablesIds'];
    }

}
