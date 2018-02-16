<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CArgs
{

    private $args = [];

    public function __construct($arg_infos)
    {
        foreach ($arg_infos as $arg_name => $arg_info) {
            $this->args[$arg_name] = [
                'set' => false,
                'value' => null
            ];
        }
    }

    public function &__get($name)
    {
        $this->validateArg($name);

        if (!$this->args[$name]['set'])
            throw new \Exception("Api arg `{$name}` not set.");

        return $this->args[$name]['value'];
    }

    public function __isset($name)
    {
        $this->validateArg($name);

        return $this->args[$name]['set'];
    }

    public function __set($name, $value)
    {
        $this->validateArg($name);

        $this->args[$name]['set'] = true;
        $this->args[$name]['value'] = $value;
    }

    public function __exists($name)
    {
        return in_array($name, array_keys($this->args));
    }


    private function validateArg($name)
    {
        if (!in_array($name, array_keys($this->args)))
            throw new \Exception("Api arg `{$name}` does not exist.");
    }

}
