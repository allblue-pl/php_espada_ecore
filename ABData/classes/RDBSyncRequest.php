<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

abstract class RDBSyncRequest extends RRequest {
    public function __construct(CDataStore $dataStore) {
        parent::__construct($dataStore);
    }


    abstract public function getDeviceRowIds(CDevice $device) : array;
}