<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CResult extends CResult_Base {

    static public function Success($message = '')
    {
        return new CResult(self::SUCCESS, $message);
    }

    static public function Failure($message = '')
    {
        return new CResult(self::FAILURE, $message);
    }

    static public function Error($message = '')
    {
        return new CResult(self::ERROR, $message);
    }


    private $outputs = [];

    
    public function __construct($result, $message)
    {
        parent::__construct($result, $message);
    }

    public function add($name, $value)
    {
        if ($name === 'result' || $name == 'message' || $name === 'debug' ||
                array_key_exists($name, $this->outputs))
            throw new \Exception("Output '{$name}' already exists.");

        $this->outputs[$name] = $value;

        return $this;
    }

    public function exists($name)
    {
        return array_key_exists($name, $this->outputs);
    }

    public function get($name)
    {
        if (!$this->exists($name))
            throw new \Exception("Output '{$name}' does not exist.");

        return $this->outputs[$name];
    }

    public function getJSON()
    {
        // $json = mb_convert_encoding($this->outputs, 'UTF-8', 'UTF-8');

        $this->escapeNonUTF($this->outputs);

        $json = $this->outputs;
        $json['result'] = $this->getResult();
        $json['message'] = $this->getMessage();
        $json['debug'] = $this->getDebug();

        if (EDEBUG)
            $json_string = json_encode($json, JSON_PRETTY_PRINT);
        else
            $json_string = json_encode($json);

        if ($json_string == null) {
            throw new \Exception('Cannot parse Api\CResult `outputs`: ' .
                    json_last_error_msg());
        }

        return $json_string;
    }

    public function isset($name)
    {
        return array_key_exists($name, $this->outputs);
    }


    private function escapeNonUTF(array &$json)
    {   
        array_walk_recursive($json, function(&$item) {
            if (is_string($item))
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        });
    }

}
