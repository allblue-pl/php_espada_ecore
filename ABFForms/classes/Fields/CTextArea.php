<?php namespace EC\SPKForms\Fields;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\SPKForms;

class HTextArea extends SPKForms\VField {

    public function __construct($name, $placeholder = '', $label = '',
            $default_value = '') {
        parent::__construct('textArea', $name, $label);

        $abf_object = $this->getObject();

        $abf_object->placeholder = $placeholder;
        $abf_object->defaultValue = $default_value;
    }

}
