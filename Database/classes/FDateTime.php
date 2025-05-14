<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FDateTime extends FField {

    public function __construct($is_null)
    {
        parent::__construct($is_null);
    }

    public function getVField($info = [])
    {
        return new EC\Forms\VDate(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value)
    {
        return $db->escapeTime_DateTime($value);
    }

    protected function _parse($value)
    {
        if ($value === null)
            return null;
            
        return (float)round($value);
    }

    protected function _unescape(EC\MDatabase $db, $value)
    {
        return $db->unescapeTime_DateTime($value);
    }

}
