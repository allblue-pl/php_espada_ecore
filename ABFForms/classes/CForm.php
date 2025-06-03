<?php namespace EC\SPKForms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CForm {

    private $name = '';

    private $fields = [];
    private $object = null;

    public function __construct($name) {
        $this->name = $name;

        $this->object = new EC\SPK\CObject();

        /* Fields */
        $this->object->fields = [];
        /* Values */
        $this->object->values = new EC\SPK\CObject();
        /* Validator */
        $this->object->validator = new EC\SPK\CObject();
        $this->object->validator->validated = false;
        $this->object->validator->fields = new EC\SPK\CObject();
    }

    public function add(CField $field) {
        $this->object->fields[$field->getName()] = $field->getObject();
    }

    public function getObject() {
        return $this->object;
    }

    public function initialize(AngularJSEModule $angular_js) {
        foreach ($this->fields as $field) {
            $field->initialize($angular_js, $field_id);

            $field_object = $field->getObject();
            $field_object->id = $field_id;

            $this->object->fields->set($field->getName(), $field_object);
            $this->add_AddValidator($field->getName());
        }
    }

    private function add_AddValidator($name) {
        $field_validator =
            $this->object->validator->fields->set($name, new EC\SPK\CObject());

        $field_validator->errors = [];
        $field_validator->warnings = [];
        $field_validator->successes = [];
    }

}
