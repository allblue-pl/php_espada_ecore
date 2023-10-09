<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Sys,
    EC\Api\CArgs, EC\Api\CResult;

class CDataStore
{

    const Response_Types_Success = 0;
    const Response_Types_ResultFailure = 1;
    const Response_Types_ResultError = 2;
    const Response_Types_ActionError = 3;
    const Response_Types_Error = 4;

    private $db = null;
    private $requests = null;
    
    private $tableRequests = null;


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

    public function getUpdateData(CDevice $device, ?int $schemeVersion, 
            $rDeviceDeletedRows, &$error)
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
            $where = [];

            if ($lastUpdate !== null) {
                $where = [
                    [ '_Modified_DateTime', '>=', $lastUpdate ],
                ];
            }

            $actionResult = $tableRequest->executeAction($device, 'select', [
                'where' => $where,
            ], $schemeVersion);

            if ($actionResult['_type'] !== 0) {
                $error = "Cannot execute table '{$tableName}' request action '': " . 
                        $actionResult['_message'];
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

        /* Update DeviceRows */
        $deviceRows_DeletePairs = [ 'OR' => [] ];
        foreach ($rDeviceDeletedRows as $rDeviceDeletedRow) {
            $deviceRows_DeletePairs['OR'][] = [ 'AND' => [
                [ 'TableId', '=', $rDeviceDeletedRow[0] ],
                [ 'RowId', '=', $rDeviceDeletedRow[1] ],
            ]];
        }

        if (count($deviceRows_DeletePairs['OR']) > 0) {
            if (!(new TDeviceRows($this->db))->delete_Where([
                [ 'DeviceId', '=', $device->getId() ],
                $deviceRows_DeletePairs,
                    ]))
                throw new \Exception('Cannot delete device rows.');
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

        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $localTransaction = true;
            $this->db->transaction_Start();
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
        $response = [
            'type' => self::Response_Types_Success,
            'errorMessage' => null,
            'info' => [ '_stdObj' => '' ],
            'results' => [ '_stdObj' => '' ],
            'requestIds' => [],
            'actionErrors' => [ '_stdObj' => '' ],
        ];

        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $localTransaction = true;
            $this->db->transaction_Start();
        }

        $success = true;

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
                    $schemeVersion) = $dbRequest;

            if (in_array($dbRequestId, $deviceRequestIds_Processed))
                continue;

            $response['results'][$dbRequestId] = null;
            $response['requestIds'][] = $dbRequestId;
            $response['actionErrors'][$dbRequestId] = null;

            $result = null;

            try {
                $result = $this->getRequest($dbRequestName)
                        ->executeAction($device, $actionName, $actionArgs, $schemeVersion);
            } catch (\Exception $e) {
                if (EDEBUG)
                throw $e;

                $success = false;
                
                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "Action Error: '{$dbRequestName}:{$actionName}'";
                $response['actionErrors'][$dbRequestId] = $e->getMessage();
                break;
            }

            if (!is_array($result)) {
                $success = false;

                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "Action result is not an array: " .
                        "'{$dbRequestName}:{$actionName}'";
                $response['actionErrors'][$dbRequestId] = 
                        "Action result is not an array.";

                break;
            }

            if (!array_key_exists('_type', $result)) {
                $success = false;

                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "No '_type' in action result: " .
                        "'{$dbRequestName}:{$actionName}'";
                $response['actionErrors'][$dbRequestId] = 
                        "No '_type' in action result.";

                break;
            }

            $response['results'][$dbRequestId] = $result; 

            if ($result['_type'] >= 2) {    
                $success = false;

                $response['type'] = self::Response_Types_ResultError;
                $response['errorMessage'] = "Result Error -> " .
                        "'{$dbRequestName}:{$actionName}'";

                break;
            }

            if ($result['_type'] === 1) {    
                $success = false;

                $response['type'] = self::Response_Types_ResultFailure;
                $response['errorMessage'] = "Result Failure -> " .
                        "'{$dbRequestName}:{$actionName}'";

                break;
            }

            $rDeviceRequests[] = [
                'DeviceId' => $device->getId(),
                'RequestId' => $dbRequestId,
            ];
        }

        if ($success) {
            if (!(new TDeviceRequests($this->db))->update($rDeviceRequests)) {
                $success = false;
            
                $response['type'] = self::Response_Types_Error;
                $response['errorMessage'] = "Cannot update device requests.";
            }
        }

        if ($success) {
            if (!$device->update($this->db)) {
                $success = false;

                $response['type'] = self::Response_Types_Error;
                $response['errorMessage'] = "Cannot update device.";
            }
        }

        if ($localTransaction) {
            if (!$this->db->transaction_Finish($success)) {
                $response['type'] = self::Response_Types_Error;
                $response['errorMessage'] = "Cannot commit changes to db.";
            }
        }

        return $response;
    }

    public function processRequests(CDevice $device, array $requests)
    {
        $response = [
            'actionErrors' => [ '_stdObj' => '' ],
            'type' => self::Response_Types_Success,
            'errorMessage' => null,
            'info' => [ '_stdObj' => '' ],
            'results' => [ '_stdObj' => '' ],
            'requestIds' => [],
        ];

        $localTransaction = false;
        if ($this->db->transaction_IsAutocommit()) {
            $localTransaction = true;
            $this->db->transaction_Start();
        }

        $success = true;

        foreach ($requests as $request) {
            list($requestId, $requestName, $actionName, $actionArgs, 
                    $schemeVersion) = $request;

            $response['results'][$requestId] = null;
            $response['requestIds'][] = $requestId;
            $response['actionErrors'][$requestId] = null;

            $result = null;

            try {
                $result = $this->getRequest($requestName)
                        ->executeAction($device, $actionName, $actionArgs, 
                        $schemeVersion);
            } catch (\Exception $e) {
                if (EDEBUG)
                    throw $e;

                $success = false;

                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "Action Error: '{$requestName}:{$actionName}'";
                $response['actionErrors'][$requestId] = $e->getMessage();
                break;
            }
            
            if (!is_array($result)) {
                $success = false;

                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "Action result is not an array: " .
                        "'{$requestName}:{$actionName}'";
                $response['actionErrors'][$requestId] = 
                        "Action result is not an array.";

                break;
            }

            if (!array_key_exists('_type', $result)) {
                $success = false;

                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "No '_type' in action result: " .
                        "'{$requestName}:{$actionName}'";
                $response['actionErrors'][$requestId] = 
                        "No '_type' in action result.";

                break;
            }

            $response['results'][$requestId] = $result;

            if ($result['_type'] >= 2) {    
                $success = false;

                $response['type'] = self::Response_Types_ResultError;
                $response['errorMessage'] = "Result Error: " .
                        "'{$requestName}:{$actionName}'";

                break;
            }

            if ($result['_type'] === 1) {    
                $success = false;

                $response['type'] = self::Response_Types_ResultFailure;
                $response['errorMessage'] = "Result Failure: " .
                        "'{$requestName}:{$actionName}'";

                break;
            }
        }

        if ($success) {
            if (!$device->update($this->db)) {
                $success = false;

                $response['type'] = self::Response_Types_Error;
                $response['errorMessage'] = "Cannot update device.";
            }
        }

        if ($localTransaction) {
            if (!$this->db->transaction_Finish($success)) {
                $response['type'] = self::Response_Types_Error;
                $response['errorMessage'] = "Cannot commit changes to db.";
            }
        }

        return $response;
    }

}