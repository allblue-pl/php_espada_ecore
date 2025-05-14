<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class AABData {

    private $requests = null;

    public function __construct()
    {
        $this->requests = [];
    }

    public function setAction(string $actionName, callable $actionFn)
    {
        if (array_key_exists($actionName, $this->actions))
            throw new \Exception("Action '{$actionName}' already exists.");

        $this->actions[$actionName] = $actionFn;
    }

}