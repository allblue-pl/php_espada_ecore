<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CConfig {

    private $packageName = null;

    public function __construct($package_name) {
        $this->packageName = $package_name;
    }

    public function __get($name) {
        return $this->getRequired($name);
    }

    public function get($name, $default_value) {
        return HConfig::Get($this->packageName, $name, $default_value);
    }

    public function getRequired($name) {
        return HConfig::GetRequired($this->packageName, $name);
    }

}
