<?php namespace EC\SPKForms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

abstract class CField
{

    private $name = '';
    private $object = null;

    public function __construct($type, $name, $label)
    {
        $this->name = $name;
        $this->object = new EC\SPK\CObject();

        $this->object->type = $type;
        $this->object->name = $name;
        $this->object->changed = true;
        $this->object->label = $label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function &getObject()
    {
        return $this->object;
    }

}
