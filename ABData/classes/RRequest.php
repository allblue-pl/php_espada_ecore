<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class RRequest {
    private CDataStore $dataStore;
    private array $actions;

    public function __construct(CDataStore $dataStore) {
        $this->dataStore = $dataStore;
        $this->actions = [];
    }

    public function executeAction(?CDevice $device, string $actionName, 
            array $actionArgs, ?int $schemeVersion, ?float $lastUpdate) {
        if (!array_key_exists($actionName, $this->actions))
            throw new \Exception("Action '{$actionName}' does not exists.");

        return $this->actions[$actionName]['fn']($device, $actionArgs, 
                $schemeVersion, $lastUpdate);
    }

    public function getAction(string $actionName): array {
        if (!array_key_exists($actionName, $this->actions))
            throw new \Exception("Action '{$actionName}' does not exists.");

        return $this->actions[$actionName];
    }

    /**
     * 
     * @param string $actionName 
     * @return "r"|"w"
     */
    public function getActionType(string $actionName): string {
        $action = $this->getAction($actionName);

        return $action["type"];
    }

    public function getDS() {
        return $this->getDataStore();
    }

    public function getDataStore() {
        return $this->dataStore;
    }

    public function hasAction(string $actionName) {
        return array_key_exists($actionName, $this->actions);
    }

    public function setA(string $actionName, string $type, \Closure $actionFn) {
        $this->setAction($actionName, $type, $actionFn);
    }

    /**
     * 
     * @param string $actionName 
     * @param "r"|"w" $type 
     * @param \Closure $actionFn 
     * @return void 
     * @throws \Exception 
     */
    public function setAction(string $actionName, string $type, \Closure $actionFn) {
        if (array_key_exists($actionName, $this->actions))
            throw new \Exception("Action '{$actionName}' already exists.");

        $this->actions[$actionName] = [
            "fn" => $actionFn,
            "type" => $type,
        ];
    }
}