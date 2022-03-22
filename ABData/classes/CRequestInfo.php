<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CRequestInfo
{

    public static function Create(int $deviceId, $deviceHash,
            int $lastUpdate)
    {
        return new RequestInfo($deviceId, $lastUpdate);
    }


    private $deviceId = null;
    private $lastUpdate = null;


    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    public function isDBSync()
    {
        
    }


    private function __construct(int $deviceId, int $lastUpdate)
    {
        $this->deviceId = $deviceId;
        $this->lastUpdate = $lastUpdate;
    }

}