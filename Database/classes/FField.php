<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

abstract class FField {

    private $notNull = false;

    public function __construct($not_null = false)
    {
        $this->notNull = $not_null;
    }

    public function escape(EC\MDatabase $db, $value)
    {
        return $this->_escape($db, $value);
    }

    public function isNotNull()
    {
        return $this->notNull;
    }

    public function parse($value)
    {
        return $this->_parse($value);
    }

    public function unescape(EC\MDatabase $db, $value)
    {
        return $this->_unescape($db, $value);
    }

    abstract public function getVField($info = []);

    abstract protected function _escape(EC\MDatabase $db, $value);
    abstract protected function _parse($value);
    abstract protected function _unescape(EC\MDatabase $db, $value);

}
