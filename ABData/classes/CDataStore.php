<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Sys,
    EC\Api\CArgs, EC\Api\CResult;

class CDataStore
{

    private $db = null;
    private $requests = null;


    public function __construct(EC\MDatabase $db, array $tableRequests)
    {
        $this->db = $db;
        $this->requests = [];

        $this->tableRequests = $tableRequests;

        // $ds->addRequest('Sys_TestUsers', TTestUsers);
        // $ds->addRequest('Sys_TestItems', TTestItems);
    }

    public function getDB()
    {
        return $this->db;
    }

    public function getRequest(string $requestName)
    {
        if (!$this->hasRequest($requestName))
            throw new \Exception("Request '{$requestName}' does not exist.");

        return $this->requests[$requestName];
    }

    public function getUpdateData(CDevice $device, &$error)
    {
        $updateData = [
            'update' => [],
            'delete' => [],
        ];

        $lastUpdate = $device->getLastUpdate();
        $deviceRows_New = [];

        foreach ($this->tableRequests as $tableName => $tableRequest) {
            if (!($tableRequest instanceof RRequest))
                throw new \Exception("Table request '{$tableName}' is not an instance of 'RRequest'.");

            if (!($tableRequest->hasAction('select')))
                throw new \Exception("Table request '{$tableName}' does not have action 'select'.");

            $rows = [];

            $join = null;
            $where = null;

            if ($lastUpdate !== null) {
                $where = [
                    [ '_Modified_DateTime', '>=', $lastUpdate ],
                ];
            }

            $actionResult = $tableRequest->executeAction($device, 'select', [
                'where' => $where,
            ], null);

            if ($actionResult['error'] !== null) {
                $error = "Cannot execute table '{$tableName}' request action '': " . 
                        $actionResult['error'];
                return null;
            }

            if (!array_key_exists('rows', $actionResult))
                throw new \Exception("No 'rows' in table '{$tableName}' request action 'select'.");

            $rows = $actionResult['rows'];
            $tableId = HABData::GetTableId($tableName);
            foreach ($rows as $row) {
                $deviceRows_New[] = [
                    'DeviceId' => $device->getId(),
                    'TableId' => $tableId,
                    'RowId' => $row['_Id'],
                ];
            }

            if (count($rows) > 0)
                $updateData['update'][$tableName] = $rows;
        }

        /* Deleted Rows */
        $rDeletedRows = [];
        if ($lastUpdate !== null) {
            $where[] = [ 'DeviceId', '=', $device->getId() ];

            $rDeletedRows = (new TDeletedRows($this->db))->select_Where($where);

            foreach ($rDeletedRows as $row) {
                if (!array_key_exists($row['TableId'], $updateData['delete']))
                    $updateData['delete'][$row['TableId']] = [];
    
                $updateData['delete'][$row['TableId']][] = $row['RowId'];
            }
        }

        /* Update DeviceRows */
        $deviceRows_DeletePairs = [ 'OR' => [] ];
        foreach ($rDeletedRows as $tDeletedRow) {
            $deviceRows_DeletePairs['OR'][] = [ 'AND' => [
                [ 'TableId', '=', $tDeletedRow['TableId'] ],
                [ 'RowId', '=', $tDeletedRow['RowId'] ],
            ]];
        }

        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $localTransaction = true;
            $this->db->transaction_Start();
        }

        if (count($deviceRows_DeletePairs) > 0) {
            if (!(new TDeviceRows($this->db))->delete_Where([
                [ 'DeviceId', '=', $device->getId() ],
                $deviceRows_DeletePairs,
                    ]))
                throw new \Exception('Cannot delete device rows.');
        }

        if (!(new TDeviceRows($this->db))->update($deviceRows_New))
            throw new \Exception('Cannot update device rows.');

        if ($localTransaction) {
            if (!$this->db->transaction_Finish(true))
                throw new \Exception('Cannot commit.');
        }

        return $updateData;
    }

    public function hasRequest(string $requestName)
    {
        return array_key_exists($requestName, $this->requests);
    }

    public function setR(string $requestName, RRequest $request)
    {
        $this->setRequest($requestName, $request);
    }

    public function setRequest(string $requestName, RRequest $request)
    {
        if (array_key_exists($requestName, $this->requests))
            throw new \Exception("Request '{$requestName}' already exists.");

        $this->requests[$requestName] = $request;
    }

    public function processDBRequests(CDevice $device, array $dbRequests)
    {
        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $localTransaction = true;
            $this->db->transaction_Start();
        }

        $success = true;
        $requestError = null;
        $error = null;

        $dbRequestIds = array_column($dbRequests, 0);
        
        $rDeviceRequests_Processed = [];
        if (count($dbRequestIds)) {
            $rDeviceRequests_Processed = (new TDeviceRequests($this->db))->select_Where([
                [ 'DeviceId', '=', $device->getId() ],
                [ 'RequestId', 'IN', $dbRequestIds ],
            ], 'FOR UPDATE');
            $deviceRequestIds_Processed = array_column($rDeviceRequests_Processed, 
                    'RequestId');
        }

        $rDeviceRequests = [];
        foreach ($dbRequests as $dbRequest) {
            /* Legacy Fix */
            if (count($dbRequest) === 4)
                $dbRequest[] = null;
            /* / Legacy Fix */

            list($dbRequestId, $dbRequestName, $actionName, $actionArgs, 
                    $schemeVersion, ) = $dbRequest;

            if (in_array($dbRequestId, $deviceRequestIds_Processed))
                continue;

            $result = $this->getRequest($dbRequestName)
                    ->executeAction($device, $actionName, $actionArgs, $schemeVersion);

            if (!is_array($result)) {
                $success = false;
                $requestError = "'{$dbRequestName}:{$actionName}' -> Result is not an array.";
            
                break;
            }

            if (!array_key_exists('success', $result)) {
                $success = false;
                $requestError = "'{$dbRequestName}:{$actionName}' -> No 'success' in result.";
            
                break;
            }

            if (!$result['success']) {    
                if (!array_key_exists('error', $result))
                    $result['error'] = "No 'error' in result.";
                
                $success = false;
                $requestError = "'{$dbRequestName}:{$actionName}' -> {$result['error']}";
                break;
            }

            $rDeviceRequests[] = [
                'DeviceId' => $device->getId(),
                'RequestId' => $dbRequestId,
            ];
        }

        if ($success) {
            if (!(new TDeviceRequests($this->db))->update($rDeviceRequests))
                $success = false;
        }

        if ($success) {
            if (!$device->update($this->db))
                $success = false;
        }

        if ($localTransaction) {
            if (!$this->db->transaction_Finish($success))
                throw new \Exception('Cannot commit');
        }

        return [
            'success' => $success,
            'error' => $requestError !== null ? $requestError : $error,
        ];
    }

    public function processRequests(CDevice $device, array $requests)
    {
        $response = [];

        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $localTransaction = true;
            $this->db->transaction_Start();
        }

        $success = true;

        foreach ($requests as $request) {
            list($requestId, $requestName, $actionName, $actionArgs, 
                    $schemeVersion) = $request;

            $result = $this->getRequest($requestName)
                    ->executeAction($device, $actionName, $actionArgs, 
                    $schemeVersion);
            
            $response[$requestId] = $result;

            if (!$result['success']) {
                $success = false;
                break;
            }
        }

        if ($success) {
            if (!$device->update($this->db))
                $success = false;
        }

        if ($localTransaction) {
            if (!$this->db->transaction_Finish($success))
                throw new \Exception('Cannot commit.');
        }

        return $response;
    }

}