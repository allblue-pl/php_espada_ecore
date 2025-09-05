<?php namespace EC\CSV;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CCSVCreator {
    private $separator;
    private $separator_Replacement;

    private $csv;

    public function __construct() {
        $this->separator = ';';
        $this->separator_Replacement = ',';

        $this->csv = '';
    }

    public function addColumn($value) {
        $value = str_replace('"', '\\"', $value);
        $value = str_replace($this->separator, 
                $this->separator_Replacement,  $value);

        $this->csv .= "\"{$value}\"" . $this->separator;
    }

    public function newRow() {
        $this->csv .= "\r\n";
    }

    public function getContent() {
        return $this->csv;
    }
}