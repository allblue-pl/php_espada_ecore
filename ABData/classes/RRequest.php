<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class RRequest
{

    private $dataStore = null;
    private $actions = null;

    public function __construct(CDataStore $dataStore)
    {
        $this->dataStore = $dataStore;
        $this->actions = [];
    }

    public function executeAction(CDevice $device, 
            string $actionName, array $actionArgs)
    {
        if (!array_key_exists($actionName, $this->actions))
            throw new \Exception("Action '{$actionName}' does not exists.");

        return $this->actions[$actionName]['fn']($device, $actionArgs);
    }

    public function getDS()
    {
        return $this->getDataStore();
    }

    public function getDataStore()
    {
        return $this->dataStore;
    }

    public function hasAction(string $actionName)
    {
        return array_key_exists($actionName, $this->actions);
    }

    public function setA(string $actionName, callable $actionFn)
    {
        $this->setAction($actionName, $actionFn);
    }

    public function setAction(string $actionName, callable $actionFn)
    {
        if (array_key_exists($actionName, $this->actions))
            throw new \Exception("Action '{$actionName}' already exists.");

        $this->actions[$actionName] = [
            'fn' => $actionFn
        ];
    }

}