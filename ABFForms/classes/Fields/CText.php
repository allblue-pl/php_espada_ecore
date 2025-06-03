<?php namespace EC\SPKForms\Fields;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\SPKForms;

class HText extends SPKForms\VField {

    public function __construct($name, $label = '',
            $default_value = '') {
        parent::__construct('text', $name, $label);

        $abf_object = $this->getObject();

        $abf_object->defaultValue = $default_value;
    }

}
