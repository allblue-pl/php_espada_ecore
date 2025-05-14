<?php namespace EC\Database;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class FText extends FField {

    private $types = [
        'tiny'      => 256,
        'regular'   => 65535,
        'medium'    => 16777215,
    ];

    private $type = null;

    public function __construct($is_null, $type = 'regular')
    {
        parent::__construct($is_null);

        if (!array_key_exists($type, $this->types))
            throw new \Exception("Type `{$type}` does not exist.");

        $this->type = $type;
    }

    public function getVField($info = [])
    {
        return new EC\Forms\VText(array_merge([
            'notNull' => $this->isNotNull(),
            'maxLength' => $this->types[$this->type]
        ], $info));
    }

    protected function _escape(EC\MDatabase $db, $value)
    {
        return $db->escapeString($value);
    }

    protected function _parse($value)
    {
        if ($value === null)
            return null;
            
        return (string)$value;
    }

    protected function _unescape(EC\MDatabase $db, $value)
    {
        return $db->unescapeString($value);
    }

}
