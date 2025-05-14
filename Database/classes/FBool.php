<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FBool extends FField {

    public function __construct($is_null)
    {
        parent::__construct($is_null);
    }

    public function getVField($info = [])
    {
        return new EC\Forms\VBool(array_merge([
            'notNull' => $this->isNotNull()
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value)
    {
        return $db->escapeBool($value);
    }

    protected function _parse($value)
    {
        if ($value === null)
            return null;

        return (bool)$value;
    }

    protected function _unescape(EC\MDatabase $db, $value)
    {
        return $db->unescapeBool($value);
    }

}
