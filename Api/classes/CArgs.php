<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CArgs {

    private $args = [];

    public function __construct($argInfos) {
        foreach ($argInfos as $argName => $argInfo) {
            if ($argName === 'get' || $argName === 'getAll' || $argName === 'set')
                throw new \Exception("'{$argName}' is a reserved arg name.");

            $this->args[$argName] = [
                'set' => false,
                'value' => null
            ];
        }
    }

    public function exists($name) {
        return in_array($name, array_keys($this->args));
    }

    public function &get($name) {
        $this->validateArg($name);

        if (!$this->args[$name]['set'])
            throw new \Exception("Api arg `{$name}` not set.");

        return $this->args[$name]['value'];
    }

    public function getAll() {
        $args = [];
        foreach ($this->args as $argName => $argInfo) {
            if (!$argInfo['set'])
                continue;

            $args[$argName] = $argInfo['value'];
        }

        return $args;
    }

    public function isset($name) {
        $this->validateArg($name);

        return $this->args[$name]['set'];
    }

    public function &__get($name) {
        return $this->get($name);
    }

    public function __isset($name) {
        return $this->isset($name);
    }

    public function __set($name, $value) {
        $this->validateArg($name);

        $this->args[$name]['set'] = true;
        $this->args[$name]['value'] = $value;
    }

    public function __exists($name) {
        return $this->exists($name);
    }


    private function validateArg($name) {
        if (!in_array($name, array_keys($this->args)))
            throw new \Exception("Api arg `{$name}` does not exist.");
    }

}
