<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FTime extends FField {

    public function __construct($isNull) {
        parent::__construct($isNull);
    }

    public function getVField($info = []) {
        return new EC\Forms\VTime(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    protected function _escape(MDatabase $db, $value) {
        return $db->escapeLong($value);
    }

    protected function _parse($value) {
        if ($value === null)
            return null;
            
        return (float)round($value);
    }

    protected function _unescape(MDatabase $db, $value) {
        return $db->unescapeLong($value);
    }

}
