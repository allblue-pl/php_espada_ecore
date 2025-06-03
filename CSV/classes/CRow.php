<?php namespace EC\CSV;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CRow {

    private $columns = [];

    public function __construct() {

    }

    public function addColumn($value) {
        $this->columns[] = $value;
    }

    public function getColumnsLength() {
        return count($this->columns);
    }

    public function getColumn($i) {
        if ($i < 0 || $i >= $this->getColumnsLength()) {
            throw new \Exception("Cannot read column {$i} in :" .
                    print_r($this->columns, true));
        }

        return $this->columns[$i];
    }

}
