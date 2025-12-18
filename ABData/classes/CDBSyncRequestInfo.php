<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CDBSyncRequestInfo {
    public string $tableName;
    public RDBSyncRequest $request;

    public function __construct(string $tableName, RDBSyncRequest $request) {
        $this->tableName = $tableName;
        $this->request = $request;
    }
}