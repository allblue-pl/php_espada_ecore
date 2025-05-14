<?php namespace EC\SPKForms\Fields;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\SPKForms;

class CRadio extends SPKForms\VField {

    public function __construct($name, $label = '')
    {
        parent::__construct('radio', $name, $label);

        $abf_object = $this->getObject();

        $abf_object->options = [];
    }

    public function addOption($text, $value)
    {
        $abf_object = $this->getObject();

        $option = new EC\SPK\CObject();
        $option->text = $text;
        $option->value = $value;
        $option->id = $this->getName() . '_' . count($abf_object->options);

        $abf_object->options[] = $option;
    }

}
