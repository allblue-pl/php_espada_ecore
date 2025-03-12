<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CResult_Base
{

    const SUCCESS       = 0;
    const FAILURE       = 1;
    const ERROR         = 2;

    static public function Success_Base($type, $message = '')
    {
        if ($type === 'json')
            return new CResult(self::SUCCESS, $message);
        else if ($type === 'bytes')
            return new CResult_Bytes(self::SUCCESS, $message);
        
        throw new \Exception('Unknown result type.');
    }

    static public function Failure_Base($type, $message = '')
    {
        if ($type === 'json')
            return new CResult(self::FAILURE, $message);
        else if ($type === 'bytes')
            return new CResult_Bytes(self::FAILURE, $message);
        
        throw new \Exception('Unknown result type.');
    }

    static public function Error_Base($type, $message = '')
    {
        if ($type === 'json')
            return new CResult(self::ERROR, $message);
        else if ($type === 'bytes')
            return new CResult_Bytes(self::ERROR, $message);

        throw new \Exception('Unknown result type.');
    }


    private $result;
    private $message;
    private $compress;
    private $debug;

    public function __construct(int $result, string $message)
    {
        $this->result = $result;
        $this->message = $message;
        $this->compress = false;
        $this->debug = null;
    }

    public function compress(bool $compress) {
        $this->compress = $compress;

        return $this;
    }

    public function debug($message)
    {
        if (EDEBUG) {
            if ($this->debug === null)
                $this->debug = [];

            $this->debug[] = $message;
        }

        return $this;
    }

    public function isCompressed() {
        return $this->compress;
    }

    public function isSuccess()
    {
        return $this->result === self::SUCCESS;
    }

    public function isFailure()
    {
        return $this->result === self::FAILURE;
    }

    public function isError()
    {
        return $this->result === self::ERROR;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getResult()
    {
        return $this->result;
    }

}
