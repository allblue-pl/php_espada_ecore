<?php namespace EC\SPKForms\Fields;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\SPKForms;

class CInput extends SPKForms\VField {

    public function __construct($name, $type, $placeholder = '', $label = '',
            $default_value = '') {
        parent::__construct('input', $name, $label);

        $abf_object = $this->getObject();

        $abf_object->inputType = $type;
        $abf_object->placeholder = $placeholder;
        $abf_object->defaultValue = $default_value;
    }

}
