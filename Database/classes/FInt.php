<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FInt extends FField {

    public function __construct($not_null, bool $unsigned = false) {
        parent::__construct($not_null);
    }

    public function getVField($info = []) {
        return new EC\Forms\VInt(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value) {
        return $db->escapeInt($value);
    }

    protected function _parse($value) {
        if ($value === null)
            return null;
            
        return (int)$value;
    }

    protected function _unescape(EC\MDatabase $db, $value) {
        return $db->unescapeInt($value);
    }

}
