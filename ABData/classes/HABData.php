<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HABData
{

    static public function AddDeletedRows(CDevice $device, 
            EC\Database\TTable $table, array $rows)
    {
        $db = $table->getDB();

        $deleteRows = [];
        foreach ($rows as $row) {
            $deleteRow = [
                'TableId' => self::GetTableId($table->getTableName()),
                'RowId' => $row['_Id'],
            ];
            self::ParseUpdateRow($device, $deleteRow);

            $deleteRows[] = $deleteRow;
        }

        return (new EC\ABData\TDeletedRows($db))->update($deleteRows);
    }

    static public function ClearDeviceRows_ByLastSync(EC\Database\MDatabase $db,
            float $beforeTime)
    {
        $localTransaction = false;
        if ($db->transaction_IsAutocommit()) {
            $db->transaction_Start();
            $localTransaction = true;
        }

        $rDevices = (new TDevices($db))->select_Where([
            [ 'LastSync', '<', $beforeTime ],
            [ 'LastSync', '<>', null ],
        ]);

        $deviceIds = array_column($rDevices, 'Id');

        (new TDeviceRows($db))->delete_Where([
            [ 'DeviceId', 'IN', $deviceIds ],
        ]);

        (new TDevices($db))->update_Where([
            'LastSync' => null,
        ],[
            [ 'Id', 'IN', $deviceIds ],
        ]);

        if ($localTransaction) {
            if (!$db->transaction_Finish(true))
                throw new \Exception('Cannot commit.');
        }
    }

    static public function Delete_ByColumn(CDevice $device, EC\Database\TTable $table, 
            $columnName, $columnValue)
    {
        return self::Delete_Where($device, $table, [
            [ $columnName, '=', $columnValue ],
        ]);
    }

    static public function Delete_Where(CDevice $device, EC\Database\TTable $table,
            $whereConditions)
    {
        $db = $table->getDB();

        $localTransaction = false;
        if ($db->transaction_IsAutocommit()) {
            $db->transaction_Start();
            $localTransaction = true;
        }

        $rows = $table->select_Where($whereConditions, '');
        if (count($rows) > 0) {
            if (!$table->delete_Where($whereConditions)) {
                if ($localTransaction)
                    $db->transaction_Finish(false);
                return false;
            }
        }

        if (!self::AddDeletedRows($device, $table, $rows)) {
            if ($localTransaction)
                $db->transaction_Finish(false);
            return false;
        }

        if ($localTransaction) {
            if (!$db->transaction_Finish(true))
                return false;
        }

        return true;
    }

    static public function GetTableId(string $tableName)
    {
        if (self::$TableIds === null) {
            self::$TableIds = json_decode(file_get_contents(PATH_PRESETS . 
                    '/Sys/tableIds.json'), true);
        }

        if (!array_key_exists($tableName, self::$TableIds))
            throw new \Exception("Table '{$tableName}' does not exist.");

        return self::$TableIds[$tableName];
    }

    static public function GetTableName(int $tableId)
    {
        if (self::$TableIds === null) {
            self::$TableIds = json_decode(file_get_contents(PATH_PRESETS . 
                    '/Sys/tableIds.json'), true);
        }

        $tableName = array_search($tableId, self::$TableIds);
        if ($tableName === false)
            throw new \Exception("Table '{$tableId}' does not exist.");

        return $tableName;
    }

    static public function ParseUpdateRow(EC\ABData\CDevice $device, &$row)
    {
        $maxExecutionTime = EC\HDate::Millis_Span_Second * 
                ini_get('max_execution_time');

        $row['_Modified_DateTime'] = new EC\Database\CRawValue(
                "CAST(UNIX_TIMESTAMP(CURTIME(3)) * 1000 AS UNSIGNED) + {$maxExecutionTime}");
                
        //$device->getCreateTime();
        // $row['_Modified_DeviceId'] = $device->getId();
    }

    static public function Update(CDevice $device, EC\Database\TTable $table, 
            $rows)
    {        
        // $updatedIds = [];

        $rows_New = [];
        $rows_Existing = [];
        $rows_Existing_ByDevice = [];
        $i = -1;
        foreach ($rows as &$row) {
            $i++;
            
            if (!array_key_exists('_Id', $row))
                throw new \Exception("No '_Id' column in row '{$i}'.");

            if ($row['_Id'] === null)
                throw new \Exception("'_Id' in rows '{$i}' cannot be null.");

            self::ParseUpdateRow($device, $row);

            if ($device->isNewId($row['_Id']))
                $rows_New[] = $row;
            else
                $rows_Existing_ByDevice[] = $row;
        }

        $rows_Existsing_DB = $table->select_Where([
            [ '_Id', 'IN', array_column($rows, '_Id') ],
        ], 'FOR UPDATE');
        $rows_Existsing_DB_Ids = array_column($rows_Existsing_DB, '_Id');

        /* Skipping deleted rows. */
        foreach ($rows_Existing_ByDevice as $row_Existing_ByDevice) {
            if (!in_array($row_Existing_ByDevice['_Id'], 
                    $rows_Existsing_DB_Ids)) {
                // throw new \Exception("'_Id' '{$row_Existing['_Id']}'" .
                //         " does not exist in database.");
                continue;
            }

            $rows_Existing[] = $row_Existing_ByDevice;
        }

        foreach ($rows_New as $row_New) {
            if (in_array($row_New['_Id'], $rows_Existsing_DB_Ids)) {
                throw new \Exception("'_Id' '{$row_New['_Id']}'" .
                        " already exists in database.");
            }
        }

        if (!$table->update($rows))
            return false;

        foreach ($rows_Existing as $row_Existing) {
            $device->updateRow(self::GetTableId($table->getTableName()), 
                    $row_Existing['_Id']);
        }

        foreach ($rows_New as $row_New) {
            $device->updateRow(self::GetTableId($table->getTableName()), 
                    $row_New['_Id']);
            $device->useId($row_New['_Id']);
        }

        return true;
    }

    static public function Update_ByColumns(CDevice $device, EC\Database\TTable $table, 
            array $whenColumns, array $rows)
    {        
        $existing_Conditions = [ 'OR', []];
        $rows = array_values($rows);
        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];

            $existing_Condition = [];
            foreach ($whenColumns as $whenColumn) {
                if (!array_key_exists($whenColumn, $row)) {
                    throw new \Exception("'whenColumn' '{$whenColumn}' in row ` +
                            `'{$i}' does not exist.");
                }

                $existing_Condition[] = [ $whenColumn, '=', $row[$whenColumn] ];
            }

            $existing_Conditions[1][] = [ 'OR', $existing_Condition ];
        }
        
        $rows_Existing = $table->select_Where($existing_Conditions);
        $newIds = [];

        foreach ($rows as &$row) {
            $rowMatch = false;

            self::ParseUpdateRow($device, $row);

            foreach ($rows_Existing as $row_Existing) {
                $whenMatch = true;
                foreach ($whenColumns as $whenColumn) {
                    $column = $table->getColumn($whenColumn, true);
                    // echo $row[$whenColumn] . ' + ' . $row_Existing[$whenColumn] . ' = ' . 
                    //         ($table->parseColumnValue($whenColumn, $row[$whenColumn]) === 
                    //         $row_Existing[$whenColumn]) . '#' . "\r\n";
                    if ($table->parseColumnValue($whenColumn, $row[$whenColumn]) !== 
                            $row_Existing[$whenColumn]) {
                        $whenMatch = false;
                        break;
                    }
                }

                if ($whenMatch) {
                    $row['_Id'] = $row_Existing['_Id'];
                    $rowMatch = true;

                    break;
                }
            }

            if (!$rowMatch) {
                $row['_Id'] = $device->nextSystemId();
                $newIds[] = $row['_Id'];
            }
        }

        // print_r($rows);

        $rows_ForUpdate = [];
        for ($i = count($rows) - 1; $i >= 0; $i--) {
            $row = $rows[$i];
            $matchFound = false;
            foreach ($rows_ForUpdate as $row_ForUpdate) {
                if (self::RowsMatch($table, $whenColumns, $row, $row_ForUpdate)) {
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound)
                $rows_ForUpdate[] = $row;
        }

        if (!$table->update($rows_ForUpdate)) {
            return false;
        }

        foreach ($newIds as $newId) {
            $device->useId($newId);
        }

        return true;
    }

    static public function Update_Where(CDevice $device, EC\Database\TTable $table, 
            $values, $whereConditions)
    {
        self::ParseUpdateRow($device, $values);

        return $table->update_Where($values, $whereConditions, true);
    }

    static public function ValidateDefault_All(EC\Database\TTable $table, 
            EC\Forms\CValidator $validator, array $row, array $ignoreColumns = [])
    {
        $ignoreColumns[] = '_Modified_DateTime';

        return $table->validateDefault_All($validator, $row, $ignoreColumns);
    }   


    static private $TableIds = null;


    static private function RowsMatch($table, $whenColumns, $rowA, $rowB)
    {
        $whenMatch = true;
        foreach ($whenColumns as $whenColumn) {
            $column = $table->getColumn($whenColumn, true);
            // echo $row[$whenColumn] . ' + ' . $row_Existing[$whenColumn] . ' = ' . 
            //         ($table->parseColumnValue($whenColumn, $row[$whenColumn]) === 
            //         $row_Existing[$whenColumn]) . '#' . "\r\n";
            if ($table->parseColumnValue($whenColumn, $rowA[$whenColumn]) !== 
                    $table->parseColumnValue($whenColumn, $rowB[$whenColumn]))
                return false;
        }

        return true;
    }

}