<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CRawValue {

    private $value = null;

    public function __construct(string $value) {
        $this->value = $value;
    }   

    public function getValue() {
        return $this->value;
    }

}