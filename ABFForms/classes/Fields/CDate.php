<?php namespace EC\SPKForms\Fields;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\SPKForms;

class CDate extends SPKForms\VField
{

    private $locale = '';

    public function __construct($name, $placeholder = '', $label = '')
    {
        parent::__construct('date', $name, $label);

        $this->locale = E\Langs::Get()['code'];

        $ajs_object = $this->getObject();

        $ajs_object->placeholder = $placeholder;
        $ajs_object->locale = $this->locale;
    }

}
