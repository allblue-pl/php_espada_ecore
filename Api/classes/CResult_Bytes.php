<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CResult_Bytes extends CResult_Base {

    static public function Success($message = '') {
        return new CResult_Bytes(self::SUCCESS, $message);
    }

    static public function Failure($message = '') {
        return new CResult_Bytes(self::FAILURE, $message);
    }

    static public function Error($message = '') {
        return new CResult_Bytes(self::ERROR, $message);
    }


    private $bytes = null;

    public function __construct($result, $message) { parent::__construct($result, $message);
        $this->bytes = [];
    }

    public function add($name, $bytes) {
        $this->bytes[] = [
            'name' => $name,
            'bytes' => $bytes,
        ];

        return $this;
    }

    public function addArray($bytesArray) {
        foreach ($bytesArray as $name => $bytes)
            $this->add($name, $bytes);

        return $this;
    }

    public function getBytes() {
        return $this->bytes;
    }

}
