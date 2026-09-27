<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Strings\HStrings;

class FEnum extends FField {
    private array $values;

    public function __construct(bool $isNull, array $values) {
        parent::__construct($isNull);

        $this->values = $values;
    }

    public function getVField($info = []) {
        return new EC\Forms\VEnum(array_merge([
            'values' => $this->values,
        ], $info));
    }

    protected function _escape(MDatabase $db, $value) {
        return $db->escapeString($value);
    }

    protected function _parse($value) {
        if ($value === null)
            return null;
            
        return (string)$value;
    }

    protected function _unescape(MDatabase $db, $value) {
        return $db->unescapeString($value);
    }

}
