<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FString extends FField {

    private $maxLength = 0;

    public function __construct($is_null, $max_length) {
        parent::__construct($is_null);

        $this->maxLength = $max_length;
    }

    public function getVField($info = []) {
        return new EC\Forms\VText(array_merge([
            'notNull' => $this->isNotNull(),
            'maxLength' => $this->maxLength,
            'chars' => EC\HStrings::GetCharsRegexp([ 'digits', 'letters', 'special' ])
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value) {
        return $db->escapeString($value);
    }

    protected function _parse($value) {
        if ($value === null)
            return null;
            
        return (string)$value;
    }

    protected function _unescape(EC\MDatabase $db, $value) {
        return $db->unescapeString($value);
    }

}
