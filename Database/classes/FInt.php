<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FInt extends FField {
    private bool $unsigned;

    public function __construct($not_null, bool $unsigned = false) {
        parent::__construct($not_null);

        $this->unsigned = $unsigned;
    }

    public function getVField($info = []) {
        return new EC\Forms\VInt(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    public function isUnsigned(): bool {
        return $this->unsigned;
    }

    protected function _escape(MDatabase $db, $value) {
        return $db->escapeInt($value);
    }

    protected function _parse($value) {
        if ($value === null)
            return null;
            
        return (int)$value;
    }

    protected function _unescape(MDatabase $db, $value) {
        return $db->unescapeInt($value);
    }

}
