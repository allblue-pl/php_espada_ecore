<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CConfig_Setter {

    private $properties = [];

    public function __construct(array &$properties) {
        $this->properties = &$properties;
    }

    public function getProperties() {
        return $this->properties;
    }

    public function set(array $values) {
        $this->properties = array_replace_recursive($this->properties, $values);
    }

}
