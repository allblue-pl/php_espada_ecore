<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CDevice {
    static public $ExpirationTime       = 15 * EC\HDate::Span_Minute;
    static public $MaxDBSyncTime        = 1 * EC\HDate::Span_Minute;

    static public $Devices_Offset       = 100000000;

    static public $TempDevices_Start    = 10001;
    static public $TempDevices_End      = 11000;

    static public $FixedDevices_Start   = 20001;
    static public $FixedDevices_End     = 30000;

    static public $SystemDevice_Id = 1;


    static private $SystemDevice_Row = null;

    static private $SystemItemIds_Last = null;
    static private $SystemItemIds_Declared = [];
    static private $SystemItemIds_Used = [];


    static public function Create(EC\MDatabase $db, $deviceId, 
            $deviceHash, $lastUpdate, $declaredItemIds = [],
            &$error = null) {
        $rDevice = (new TDevices($db))->row_Where([
            [ 'Id', '=', $deviceId ],
            [ 'Id', '<>', 0 ],
            // [ 'OR' => [
            //     [ 'Expires', '>=', time() ],
            //     [ 'Expires', '=', null ],
            // ]],
        ]);

        if ($rDevice === null) {
            $error = EC\HText::_('ABData:Errors_InvalidDeviceInfo');
            return null;
        }

        if ($rDevice['Id'] <= 99999) {
            $rDevice['Hash'] = EC\HHash::GetPassword($deviceHash);
            (new TDevices($db))->update([ $rDevice ]);
        }   
        
        if (!EC\HHash::CheckPassword($deviceHash, $rDevice['Hash'])) {
            $error = EC\HText::_('ABData:Errors_InvalidDeviceInfo');
            return null;
        }

        if ($rDevice['Expires'] !== null) {
            if ($rDevice['Expires'] < time()) {
                $error = EC\HText::_('ABData:Errors_SessionExpired');
                return null;
            }
        }

        return new CDevice($db, $deviceId, $lastUpdate, [], 
                $rDevice['ItemIds_Last'], $rDevice['SystemItemIds_Last'],
                $declaredItemIds, $rDevice['Expires'], $rDevice['LastSync']);
    }

    static public function CreateNewDevice(EC\MDatabase $db, &$hash = null, 
            $fixed = false) {
        $db->requireNoTransaction();
        $db->transaction_Start();

        $table = new EC\ABData\TDevices($db);
        $hash = EC\HHash::Generate(64);
        $hash_Hashed = EC\HHash::GetPassword($hash);

        $row_Update = null;

        $expires = null;

        if ($fixed) {
            $lastDeviceId = null;
            $row_LastDeviceId = $table->row_Where([
                [ 'Id', '>=', self::ParseDeviceId(self::$FixedDevices_Start, 'fixed') ],
            ], 'ORDER BY abd_d.Id DESC', true);
            if ($row_LastDeviceId !== null) {
                if ($row_LastDeviceId['Id'] >= self::ParseDeviceId(self::$FixedDevices_End, 'fixed'))
                    throw new \Exception('Too many fixed devices.');
            }

            $row_Update = [
                'Id' => $row_LastDeviceId === null ? 
                    self::ParseDeviceId(self::$FixedDevices_Start, 'fixed') : $row_LastDeviceId['Id'] + 10,
                'ItemIds_Last' => 0,
                'SystemItemIds_Last' => 0,
                'Hash' => $hash_Hashed,
                'Expires' => $expires,
                'LastSync' => time(),
            ];
        } else {
            $expires = time() + self::$ExpirationTime;
            $newDevice = true;

            $row = $table->row_Where([
                [ 'Id', '<=', self::ParseDeviceId(self::$TempDevices_End, 'temp') ],
                [ 'Id', '>=', self::ParseDeviceId(self::$TempDevices_Start, 'temp') ],
                [ 'Expires', '<>', null ],
            ], 'ORDER BY abd_d.Id DESC', true);

            $nextDeviceId = null;
            if ($row === null) {
                $nextDeviceId = self::ParseDeviceId(self::$TempDevices_Start, 'temp');
            } else {
                $nextDeviceId = $row['Id'] + 10;
                if ($nextDeviceId > self::ParseDeviceId(self::$TempDevices_End, 'temp')) {
                    $newDevice = false;
                    
                    $row = $table->row_Where([
                        [ 'Id', '<=', self::ParseDeviceId(self::$TempDevices_End, 'temp') ],
                    ], 'ORDER BY abd_d.Expires', true);

                    if ($row['Expires'] >= time()) 
                        throw new \Exception('Too many temporary devices.');

                    $nextDeviceId = $row['Id'];
                }
            }

            $row_Update = [
                'Id' => $nextDeviceId,
                'ItemIds_Last' => $newDevice ? 0 : $row['ItemIds_Last'],
                'SystemItemIds_Last' => $newDevice ? 0 : $row['SystemItemIds_Last'],
                'Hash' => $hash_Hashed,
                'Expires' => $expires,
                'LastSync' => time(),
            ];
        }

        if ($row_Update !== null) {
            if (!$table->update([ $row_Update ])) {
                if (EDEBUG)
                    throw new Exception('Cannot update Device row.');

                $db->transaction_Finish(false);

                return null;
            }
        }

        if (!$db->transaction_Finish(true)) {
            throw new \Exception("Cannot commit 'Device::CreateNewDevice'" . 
                    " transaction.");
        }

        return new CDevice($db, $row_Update['Id'], null, [], 
                $row_Update['ItemIds_Last'], $row_Update['SystemItemIds_Last'],
                [], $row_Update['Expires'], $row_Update['LastSync']);
    }

    static public function ParseDeviceId($rawId, $type) {
        if ($type === 'temp')
            return $rawId * 10 + 1;
        if ($type === 'temp_System')
            return $rawId * 10 + 2;
        if ($type === 'fixed')
            return $rawId * 10 + 3;
        if ($type === 'fixed_System')
            return $rawId * 10 + 4;
    }

    static public function GetIdInfo($id) {
        $deviceId = (int)floor($id / self::$Devices_Offset);

        return [
            'id' => (float)$id,
            'deviceId' => (float)$deviceId,
            'itemId' => (float)($id - $deviceId * self::$Devices_Offset),
        ];
    }

    // static public function GetSystemDeviceRow(EC\MDatabase $db)
    // {
    //     if (self::$SystemDevice_Row !== null)
    //         return self::$SystemDevice_Row;

    //     $db->requireTransaction();

    //     self::$SystemDevice_Row = $tDevices->row_Where([
    //         [ 'Id', '=', self::$SystemDevice_Id ],
    //     ], '', true);

    //     if (self::$SystemDevice_Row === null) {
    //         self::$SystemDevice_Row = [
    //             'Id' => self::$SystemDevice_Id,
    //             'ItemIds_Used' => [],
    //             'ItemIds_Last' => 0,
    //             'Hash' => null,
    //             'Expires' => null,
    //         ];
    //     }

    //     return self::$SystemDevice_Row;
    // }

    // static public function IsNewId(CDevice $device, $id)
    // {
    //     if ($device->isNewDeviceId($id))
    //         return true;

    //     $idInfo = self::GetIdInfo($id);

    //     return in_array($idInfo['itemId'], self::$SystemItemIds_Declared);
    // }

    // static public function NextSystemId(EC\MDatabase $db)
    // {
    //     $db->requireTransaction();

    //     if (self::$SystemItemIds_Last === null) {
    //         $tDevices = new TDevices($db);
    //         $rDevice = self::GetSystemDeviceRow();

    //         if ($rDevice === null)
    //             self::$SystemItemIds_Last = 0;
    //         else
    //             self::$SystemItemIds_Last = $rDevice['ItemIds_Last'];
    //     }

    //     $nextItemId = ++self::$SystemItemIds_Last;
    //     self::$SystemItemIds_Declared[] = $nextItemId;

    //     return self::ParseId(self::$SystemDevice_Id, $nextItemId);
    // }

    static public function ParseId($deviceId, $id) {
        return $deviceId * self::$Devices_Offset + $id;
    }

    // static public function UpdateDevice(CDevice $device, EC\MDatabase $db)
    // {
    //     if (count(self::$SystemItemIds_Used) > 0) {
    //         $systemItemIds_LastDeclared = 0;
    //         foreach (self::$SystemItemIds_Declared as $systemItemId_Declared) {
    //             if ($systemItemId_Declared > $systemItemIds_LastDeclared)
    //                 $systemItemIds_LastDeclared = $systemItemId_Declared;
    //         }

    //         if (!$tDevices->update([[
    //             'Id' => self::$SystemDevice_Id,
    //             'ItemIds_Last' => $systemItemIds_LastDeclared,
    //             'ItemIds_Last' => $systemItemIds_LastDeclared,
    //                 ]]))
    //             throw new \Exception('Cannot update System Device.');
    //     }

    //     $device->update();
    // }

    // static public function UseId(CDevice $device, $id)
    // {
    //     if ($device->isNewDeviceId($id)) {
    //         $device->useDeviceId($id);
    //         return;
    //     }

    //     $idInfo = self::GetIdInfo($id);

    //     if (($index = array_search($idInfo['itemId'], 
    //             self::$SystemItemIds_Declared)) !== false) {
    //         unset(self::$SystemItemIds_Declared[$index]);
    //         self::$SystemItemIds_Used[] = $id;
    //         return;
    //     }

    //     throw new \Exception("'_Id' '{$id}' is not a new id.");
    // }


    private $db = null;
    private $id = null;

    private $createTime = null;
    private $lastUpdate = null;
    private $expires = null;
    private $lastSync = null;

    private $itemIds_Declared = null;
    private $itemIds_Used = null;
    private $itemIds_Last = null;

    private $systemDevice_Id = null;
    private $systemDevice_ItemIds_Declared = null;
    private $systemDevice_ItemIds_Used = null;
    private $systemDevice_ItemIds_Last = null;

    private $rowUpdates = null;


    public function dbSync_Finish() {
        if (!(new TDevices($this->db))->update([[
            'Id' => $this->id,
            'DBSync' => null,
                ]]))
            throw new \Exception("Cannot update devices 'DBSync'.");
    }

    public function dbSync_Start() {
        $this->db->requireNoTransaction();
        $this->db->transaction_Start();

        $table = new TDevices($this->db);

         $rDevice = $table->row_Where([
            [ 'Id', '=', $this->id ],
        ], '', true);
        if ($rDevice === null)
             throw new \Exception("Device '{$this->id}' does not exist.");

        if ($rDevice['DBSync'] !== null) {
            if ($rDevice['DBSync'] >= time())
                return false;
        }

        if (!$table->update([[
            'Id' => $this->id,
            'DBSync' => time() + self::$MaxDBSyncTime,
                ]]))
            throw new \Exception("Cannot update devices 'DBSync'.");

        if (!$this->db->transaction_Finish(true))
            throw new \Exception("Cannot finish update devices 'DBSync' transaction.");

        return true;
    }

    public function getCreateTime() {
        return $this->createTime;
    }

    public function getId() {
        return $this->id;
    }

    public function getLastItemId() {
        return $this->itemIds_Last;
    }

    public function getLastSync() {
        return $this->lastSync;
    }

    public function getLastUpdate() {
        return $this->lastUpdate;
    }

    public function getRowUpdates() {
        return $this->rowUpdates;
    }

    public function isNewId($id) {
        if (!is_numeric($id))
            throw new \Exception("'_Id' must be a Long.");

        $id = $id + 0;
        $idInfo = self::GetIdInfo($id);

        if ($this->isNewId_Device($idInfo))
            return true;

        if ($this->isNewId_SystemDevice($idInfo))
            return true;

        return false;
    }

    public function nextSystemId() {
        $nextSystemItemId = ++$this->systemDevice_ItemIds_Last;
        $this->systemDevice_ItemIds_Declared[] = $nextSystemItemId;

        return self::ParseId($this->systemDevice_Id, $nextSystemItemId);
    }

    public function refreshLastSync() {
        $this->lastSync = time();
    }

    public function update() {
        /* Can probably be restricted to only used ids assuming that Id has to be used during one transaction. */
        $lastDeclaredItemId = $this->itemIds_Last;
        foreach ($this->itemIds_Declared as $itemId_Declared) {
            if ($itemId_Declared > $lastDeclaredItemId)
                $lastDeclaredItemId = $itemId_Declared;
        }

        $lastSystemItemId = $this->systemDevice_ItemIds_Last;
        foreach ($this->systemDevice_ItemIds_Used as $systemDevice_ItemId_Used) {
            if ($systemDevice_ItemId_Used > $lastSystemItemId)
                $lastSystemItemId = $systemDevice_ItemId_Used;
        }

        $result = (new TDevices($this->db))->update([
            [
                'Id' => $this->id,
                'Expires' => $this->expires === null ? 
                        null : time() + self::$ExpirationTime,
                'ItemIds_Last' => $lastDeclaredItemId,
                'SystemItemIds_Last' => $lastSystemItemId,
                'LastSync' => $this->lastSync,
            ],
        ]);

        if (!$result)
            return false;

        $this->itemIds_Last = $lastDeclaredItemId;
        $this->systemDevice_ItemIds_Last = $lastSystemItemId;

        return true;
    }

    public function updateRow($tableId, $rowId) {
        $this->rowUpdates[] = [ 
            'tableId' => $tableId, 
            'rowId' => $rowId,
        ];
    }

    public function useId($id) {
        $idInfo = self::GetIdInfo($id);

        if ($this->isNewId_Device($idInfo)) {
            // if (in_array($idInfo['itemId'], $this->itemIds_Used))
            //     throw new \Exception("Device '_Id' '{$id}' used multiple times.");

            $this->itemIds_Used[] = $idInfo['itemId'];
        } else if ($this->isNewId_SystemDevice($idInfo)) {
            // if (in_array($idInfo['itemId'], $this->systemDevice_ItemIds_Used))
            //     throw new \Exception("System device '_Id' '{$id}' used multiple times.");

            $this->systemDevice_ItemIds_Used[] = $idInfo['itemId'];
        } else
            throw new \Exception("'_Id' '{$id}' is not a new id.");
    }


    private function __construct(EC\MDatabase $db, $deviceId, $lastUpdate,
            array $usedItemIds, int $lastItemId, int $lastSystemItemId,
            array $declaredItemIds, $expires, $lastSync) {
        $this->db = $db;

        $this->createTime = $db->query_Select(
                'SELECT CAST(UNIX_TIMESTAMP(CURTIME(3)) * 1000 AS UNSIGNED) AS CreateTime')[ 0]['CreateTime'];

        $this->id = (float)($deviceId + 0);
        $this->lastUpdate = $lastUpdate;
        $this->expires = $expires;
        $this->lastSync = $lastSync;

        $this->itemIds_Last = $lastItemId;
        $this->itemIds_Declared = $declaredItemIds;
        $this->itemIds_Used = $usedItemIds;

        $this->systemDevice_Id = (float)($deviceId + 1);
        $this->systemDevice_ItemIds_Last = (float)$lastSystemItemId;
        $this->systemDevice_ItemIds_Declared = [];
        $this->systemDevice_ItemIds_Used = [];

        $this->rowUpdates = [];
    }

    private function isNewId_Device($idInfo) {
        if ($idInfo['deviceId'] !== $this->id)
            return false;

        if ($idInfo['itemId'] <= $this->itemIds_Last)
            return false;

        if (in_array($idInfo['itemId'], $this->itemIds_Declared))
            return !in_array($idInfo['itemId'], $this->itemIds_Used);

        return false;
    }

    private function isNewId_SystemDevice($idInfo) {
        if ($idInfo['deviceId'] !== $this->systemDevice_Id)
            return false;

        if (in_array($idInfo['itemId'], $this->systemDevice_ItemIds_Declared))
            return !in_array($idInfo['itemId'], $this->systemDevice_ItemIds_Used);
        
        return false;
    }

}