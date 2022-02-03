<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FLong extends FField
{

    public function __construct($is_null, $bytes = 11)
    {
        parent::__construct($is_null);
    }

    public function getVField($info = [])
    {
        return new EC\Forms\VLong(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value)
    {
        return $db->escapeLong($value);
    }

    protected function _parse($value)
    {
        if ($value === null)
            return null;
            
        return (float)round($value);
    }

    protected function _unescape(EC\MDatabase $db, $value)
    {
        return $db->unescapeLong($value);
    }

}
