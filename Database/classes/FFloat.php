<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FFloat extends FField
{

    public function __construct($is_null)
    {
        parent::__construct($is_null);
    }

    public function getVField($info = [])
    {
        return new EC\Forms\VFloat(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value)
    {
        return $db->escapeFloat($value);
    }

    protected function _parse($value)
    {
        if ($value === null)
            return null;
            
        return (float)$value;
    }

    protected function _unescape(EC\MDatabase $db, $value)
    {
        return $db->unescapeFloat($value);
    }

}
