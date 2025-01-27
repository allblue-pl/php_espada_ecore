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

    const MaxRowsInData = 5000;
    // const MaxRowsInData = 4;


    static function ValidateActionResult_Select(array $actionResult, 
            string $tableName, ?string &$error) 
            : void
    {
        if (!array_key_exists('_type', $actionResult))
            throw new \Exception("Wrong request action result format (no '_type'): " . 
                "{$tableName} -> select");

        if (!array_key_exists('_message', $actionResult))
            throw new \Exception("Wrong request action result format (no '_message'): " . 
                "{$tableName} -> select");

        if ($actionResult['_type'] !== 0) {
            $error = "Cannot execute table '{$tableName}' request action '': " . 
                    $actionResult['_message'];
            return;
        }

        if (!array_key_exists('rows', $actionResult)) {
            print_r($actionResult);
            throw new \Exception("No 'rows' in table '{$tableName}' request action 'select'.");
        }
    }

    
    private $db = null;
    private $requests = null;
    private $maxRowsInData = null;
    
    private $tableRequests = null;


    public function __construct(EC\MDatabase $db, array $tableRequests, 
            ?int $maxRowsInData = null)
    {
        $this->db = $db;
        $this->requests = [];
        $this->maxRowsInData = $maxRowsInData === null ? 
                self::MaxRowsInData : $maxRowsInData;

        $this->tableRequests = $tableRequests;

        // $ds->addRequest('Sys_TestUsers', TTestUsers);
        // $ds->addRequest('Sys_TestItems', TTestItems);
    }

    public function dbSync_GetDataInfos()
    {

    }

    public function dbSync_GetUpdateData(CDevice $device, ?int $schemeVersion, 
            ?float $lastSync, ?array &$dataInfos, ?\Exception &$error)
    {
        $updateData = [
            'update' => [],
            'delete' => [],
        ];

        $rowsCount = 0;

        $lastUpdate = $lastSync === null ? 
                null : $device->getLastUpdate();
        $rDeviceRows_New = [];

        foreach ($this->tableRequests as $tableRequestName => $tableRequestInfo) {
            if (!is_array($tableRequestInfo))
                throw new \Exception('Table request info must be an array.');
            if (count($tableRequestInfo) !== 2)
                throw new \Exception('Table request info must be in [ string, RRequest ] format.');
            if (!is_string($tableRequestInfo[0]))
                throw new \Exception('Table name is not a string.');
            $tableName = $tableRequestInfo[0];
            if (!($tableRequestInfo[1] instanceof RRequest))
                throw new \Exception("Table request '{$tableName}' is not an instance of 'RRequest'.");
            $tableRequest = $tableRequestInfo[1];
            if (!($tableRequest->hasAction('select')))
                throw new \Exception("Table request '{$tableName}' does not have action 'select'.");

            $rows = [];
            $rowsOffset = 0;

            $rowsLimit = $this->maxRowsInData - $rowsCount;
            if ($rowsLimit > 0) {
                $rows = $this->_getUpdateData($device, $schemeVersion, 
                        $rDeviceRows_New, $updateData['delete'],
                        $tableRequestName, $tableName, $tableRequest, 
                        $lastUpdate, $rowsOffset, $rowsLimit, false, $error);

                if (count($rows) > 0) {
                    if (array_key_exists($tableName, $updateData['update'])) {
                        $updateData['update'][$tableName] = array_merge(
                                $updateData['update'][$tableName], $rows);
                    } else
                        $updateData['update'][$tableName] = $rows;
                    $rowsCount += count($rows);
                }

                if (count($rows) < $rowsLimit)
                    continue;

                $rowsOffset = $rowsLimit;
            }

            $rows = $this->_getUpdateData($device, $schemeVersion,
                    $rDeviceRows_New, $updateData['delete'],
                    $tableRequestName, $tableName, $tableRequest, $lastUpdate, 
                    $rowsOffset, null, true, $error);
            if (count($rows) > 0) {
                $dataInfos[] = [
                    'tableRequestName' => $tableRequestName,
                    'ids' => array_column($rows, '_Id'),
                ];
                $rowsCount += count($rows);
            }
        }

        /* Deleted Rows */
        $rDeletedRows = [];
        if ($lastUpdate !== null) {
            $where[] = [ 'DeviceId', '=', $device->getId() ];

            $rDeletedRows = (new TDeletedRows_ByDevice($this->db))->select_Where($where);

            foreach ($rDeletedRows as $row) {
                if (!array_key_exists($row['TableId'], $updateData['delete']))
                    $updateData['delete'][$row['TableId']] = [];
    
                $updateData['delete'][$row['TableId']][] = $row['RowId'];
            }
        }

        if (!(new TDeviceRows($this->db))->update($rDeviceRows_New))
            throw new \Exception('Cannot update device rows.');

        return $updateData;
    }

    public function dbSync_GetUpdateData_Old(CDevice $device, ?int $schemeVersion, 
            ?float $lastSync, &$error)
    {
        $updateData = [
            'update' => [],
            'delete' => [],
        ];

        $lastUpdate = $lastSync === null ? 
                null : $device->getLastUpdate();
        $deviceRows_New = [];

        foreach ($this->tableRequests as $tableRequestName => $tableRequestInfo) {
            if (!is_array($tableRequestInfo))
                throw new \Exception('Table request info must be an array.');
            if (count($tableRequestInfo) !== 2)
                throw new \Exception('Table request info must be in [ string, RRequest ] format.');
            if (!is_string($tableRequestInfo[0]))
                throw new \Exception('Table name is not a string.');
            $tableName = $tableRequestInfo[0];
            if (!($tableRequestInfo[1] instanceof RRequest))
                throw new \Exception("Table request '{$tableName}' is not an instance of 'RRequest'.");
            $tableRequest = $tableRequestInfo[1];
            if (!($tableRequest->hasAction('select')))
                throw new \Exception("Table request '{$tableName}' does not have action 'select'.");

            $rows = [];

            $join = null;
            $where = [];
            $deviceRowIds = [];

            if ($lastUpdate !== null) { 
                $deviceRowIds = $tableRequest->getDeviceRowIds($device);

                $where = [ 'OR' => [
                    [ '_Modified_DateTime', '>=', $lastUpdate ],
                    [ '_Modified_DateTime', '=', null ],
                    [ '_Id', 'NOT IN', $deviceRowIds ],
                ]];
            }

            $actionResult = $tableRequest->executeAction($device, 'select', [
                'permitted' => true,
                'where' => $where,
            ], $schemeVersion, $lastUpdate);

            self::ValidateActionResult_Select($actionResult, $tableName, $error);
            if ($error !== null)
                return null;

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

            /* Indirectly Deleted Rows */
            if ($lastUpdate !== null) {
                $where = [ 'OR' => [
                    [ '_Id', 'IN', $deviceRowIds ],
                ]];

                $actionResult = $tableRequest->executeAction($device, 'select', [
                    'columnNames' => [ '_Id' ],
                    'permitted' => false,
                    'where' => $where,
                ], $schemeVersion, $lastUpdate);

                self::ValidateActionResult_Select($actionResult, $tableName, 
                        $error);
                if ($error !== null)
                    return null;

                $rIndirectlyDeletedRows = $actionResult['rows'];
                foreach ($rIndirectlyDeletedRows as $row) {
                    if (!array_key_exists($tableId, $updateData['delete']))
                        $updateData['delete'][$tableId] = [];
                
                    $updateData['delete'][$tableId][] = $row['_Id'];
                }
            }
        }

        /* Deleted Rows */
        $rDeletedRows = [];
        if ($lastUpdate !== null) {
            $where[] = [ 'DeviceId', '=', $device->getId() ];

            $rDeletedRows = (new TDeletedRows_ByDevice($this->db))
                    ->select_Where($where);

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

    public function dbSync_GetUpdateData_FromDataInfos(CDevice $device,
            ?int $schemeVersion, array &$dataInfos, ?string &$error)
    {
        $updateData = [
            'update' => [],
        ];

        $rowsCount = 0;

        for ($i = 0; $i < count($dataInfos); $i++) {
            if (!array_key_exists('tableRequestName', $dataInfos[$i]))
                throw new \Exception("No 'tableName' in 'dataInfo.");
            if (!array_key_exists('ids', $dataInfos[$i]))
                throw new \Exception("No 'ids' in 'dataInfo.");
            $tableRequestName = $dataInfos[$i]['tableRequestName'];

            if (!array_key_exists($tableRequestName, $this->tableRequests)) {
                throw new \Exception("Table request '{$tableRequestName}'" .
                        " does not exist.");
            }

            [ $tableName, $tableRequest ] = 
                    $this->tableRequests[$tableRequestName];

            if (!($tableRequest instanceof RRequest))
                throw new \Exception("Table request '{$tableRequestName}' is not an instance of 'RRequest'.");

            if (!($tableRequest->hasAction('select')))
                throw new \Exception("Table request '{$tableRequestName}' does not have action 'select'.");

            $limit = null;
            if ($rowsCount + count($dataInfos[$i]['ids']) > 
                    $this->maxRowsInData)
                $limit = $this->maxRowsInData - $rowsCount;

            $ids = array_splice($dataInfos[$i]['ids'], 0, $limit);

            $where = [[ '_Id', 'IN', $ids ]];

            $actionResult = $tableRequest->executeAction($device, 'select', [
                'columnNames' => null,
                'limit' => null,
                'permitted' => true,
                'requestType' => 'fromDataInfos',
                'where' => $where,
            ], $schemeVersion, null);

            if (!array_key_exists('_type', $actionResult))
                throw new \Exception("Wrong request action result format (no '_type'): " . 
                    "{$tableRequestName} -> select");

            if (!array_key_exists('_message', $actionResult))
                throw new \Exception("Wrong request action result format (no '_message'): " . 
                    "{$tableRequestName} -> select");

            if ($actionResult['_type'] !== 0) {
                $error = "Cannot execute table request '{$tableRequestName}' action '': " . 
                        $actionResult['_message'];
                return null;
            }

            if (!array_key_exists('rows', $actionResult)) {
                throw new \Exception("No 'rows' in table request '{$tableRequestName}'" .
                        " action 'select'.");
            }

            $rows = $actionResult['rows'];
            if (count($rows) > 0) {
                if (array_key_exists($tableName, $updateData['update'])) {
                    $updateData['update'][$tableName] = array_merge(
                            $updateData['update'][$tableName], $rows);
                } else
                    $updateData['update'][$tableName] = $rows;        

                $rowsCount += count($rows);
            }

            if ($rowsCount >= $this->maxRowsInData)
                break;

            if (count($dataInfos[$i]['ids']) === 0) {
                array_splice($dataInfos, $i, 1);
                $i--;
            }
        }

        return $updateData;
    }

    public function dbSync_ProcessRequests(CDevice $device, array $dbRequests, 
            array $rDeviceDeletedRows, ?\Exception &$responseError = null)
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
        /* / Update DeviceRows */

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
                        ->executeAction($device, $actionName, $actionArgs, 
                        $schemeVersion, $device->getLastUpdate());
            } catch (\Exception $e) {
                if (EDEBUG)
                    throw $e;

                $success = false;
                
                $response['type'] = self::Response_Types_ActionError;
                $response['errorMessage'] = "Action Error: '{$dbRequestName}:{$actionName}'";
                $response['actionErrors'][$dbRequestId] = $e->getMessage();
                $responseError = $e;
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
                $response['message'] = "Cannot update device requests.";
            }
        }

        if ($success) {
            if ($device->getLastSync() !== null)
                $device->refreshLastSync();
            if (!$device->update($this->db)) {
                $success = false;

                $response['type'] = self::Response_Types_Error;
                $response['message'] = "Cannot update device.";
            }
        }

        if ($localTransaction) {
            if (!$this->db->transaction_Finish($success)) {
                $response['type'] = self::Response_Types_Error;
                $response['message'] = "Cannot commit changes to db.";
            }
        }

        return $response;
    }

    public function getDB()
    {
        return $this->db;
    }

    public function getRequest(string $requestName) : RRequest
    {
        if (!$this->hasRequest($requestName))
            throw new \Exception("Request '{$requestName}' does not exist.");

        return $this->requests[$requestName];
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
                        $schemeVersion, null);
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


    private function _getUpdateData(CDevice $device, int $schemeVersion, 
            array &$rDeviceRows_New, array &$updateData_Delete, 
            string $tableRequestName, string $tableName, RRequest $tableRequest, 
            ?float $lastUpdate, int $rowsOffset, ?int $rowsLimit, bool $onlyIds, 
            ?string &$error) : ?array
    {
        $deviceRowIds = [];
        $where = [];

        if ($lastUpdate !== null) {
            $deviceRowIds = $tableRequest->getDeviceRowIds($device);

            $where = [ 'OR' => [
                [ '_Modified_DateTime', '>=', $lastUpdate ],
                [ '_Modified_DateTime', '=', null ],
                [ '_Id', 'NOT IN', $deviceRowIds ],
            ]];
        }

        $actionResult = $tableRequest->executeAction($device, 'select', [
            'columnNames' => $onlyIds ? [ '_Id' ] : null,
            'limit' => [ $rowsOffset, $rowsLimit ],
            'permitted' => true,
            'requestType' => $onlyIds ? 'onlyIds' : 'raw',
            'where' => $where,
        ], $schemeVersion, $lastUpdate);

        self::ValidateActionResult_Select($actionResult, $tableRequestName, $error);
        if ($error !== null)
            return null;

        $rows = $actionResult['rows'];
        $tableId = HABData::GetTableId($tableName);
        foreach ($rows as $row) {
            $rDeviceRows_New[] = [
                'DeviceId' => $device->getId(),
                'TableId' => $tableId,
                'RowId' => $row['_Id'],
            ];
        }

        /* Indirectly Deleted Rows */
        if (!$onlyIds && $lastUpdate !== null) {
            $where = [
                [ '_Id', 'IN', $deviceRowIds ],
            ];

            $actionResult = $tableRequest->executeAction($device, 'select', [
                'columnNames' => [ '_Id' ],
                'permitted' => false,
                'requestType' => 'indirectlyDeletedRows',
                'where' => $where,
            ], $schemeVersion, $lastUpdate);

            self::ValidateActionResult_Select($actionResult, $tableName, $error);
            if ($error !== null)
                return null;

            $rIndirectlyDeletedRows = $actionResult['rows'];

            foreach ($rIndirectlyDeletedRows as $row) {
                if (!array_key_exists($tableId, $updateData_Delete))
                    $updateData_Delete[$tableId] = [];
            
                $updateData_Delete[$tableId][] = $row['_Id'];
            }
        }

        return $rows;
    }

}