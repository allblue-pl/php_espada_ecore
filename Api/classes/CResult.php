<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CResult
{

    const SUCCESS       = 0;
    const FAILURE       = 1;
    const ERROR         = 2;


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


    private function __construct($result, $message)
    {
        $this->outputs['result'] = $result;
        $this->outputs['message'] = $message;
    }

    public function add($name, $value)
    {
        $this->outputs[$name] = $value;

        return $this;
    }

    public function debug($message)
    {
        if (EDEBUG) {
            if (!array_key_exists('EDEBUG', $this->outputs))
                $this->outputs['EDEBUG'] = [];

            $this->outputs['EDEBUG'][] = $message;
        }

        return $this;
    }

    public function isSuccess()
    {
        return $this->outputs['result'] === self::SUCCESS;
    }

    public function isFailure()
    {
        return $this->outputs['result'] === self::FAILURE;
    }

    public function isError()
    {
        return $this->outputs['result'] === self::ERROR;
    }

    public function get($name)
    {
        if (!isset($this->outputs[$name]))
            return null;

        return $this->outputs[$name];
    }

    public function getJSON()
    {
        if (EDEBUG)
            $json_string = json_encode($this->outputs, JSON_PRETTY_PRINT);
        else
            $json_string = json_encode($this->outputs);

        if ($json_string == null) {
            throw new \Exception('Cannot parse Api\CResult `outputs`: ' .
                    json_last_error_msg());
        }

        return $json_string;
    }

    public function getMessage()
    {
        return $this->outputs['message'];
    }

}
