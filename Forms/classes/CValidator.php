<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CValidator {

    private $info = [
        'valid' => true,
        'fields' => [],
        'state' => '',
        'errors' => [],
    ];

    public function __construct() {

    }

    public function add(string $name, mixed &$value, array $validatorFields = []) {
        if ($this->field_Exists($name))
            throw new \Exception("Field `{$name}` already exists.");

        $this->field_Add($name, $value);

        foreach ($validatorFields as $vField)
            $this->addValidatorField($name, $vField);
    }

    public function addValidatorField($name, VField $validatorField) {
        $field = $this->field_Get($name);

        $str_value = $field['value'] === null ? '' : strval($field['value']);
        $validatorField->validate($this, $name, $field['value']);

        $fields['value'] = $str_value;
    }

    public function getInfo() {
        return $this->info;
    }

    public function error($message) {
        $this->info['valid'] = false;

        $this->info['errors'][] = $message;
    }   

    public function fieldError($field_name, $message = null) {
        $this->info['valid'] = false;

        $field = &$this->field_Get($field_name);

        if (!$field['valid'])
            return;

        $field['valid'] = false;
        $field['state'] = 'error';

        if ($message !== null) {
            if (!array_key_exists('errors', $field))
                $field['errors'] = [];

            $field['errors'][] = $message;
        }
    }

    public function fieldSuccess($field_name, $message = null) {
        $field = &$this->field_Get($field_name);

        $field['valid'] = false;

        if ($field['state'] !== 'error' && $field['state'] !== 'warning')
            $field['state'] = 'success';

        if ($message !== null) {
            if (!array_key_exists('successes', $field))
                $field['successes'] = [];

            $field['successes'][] = $message;
        }
    }

    public function fieldWarning($field_name, $message = null) {
        $field = &$this->field_Get($field_name);

        $field['valid'] = false;

        if ($field['state'] !== 'error')
            $field['state'] = 'warning';

        if ($message !== null) {
            if (!array_key_exists('warnings', $field))
                $field['warnings'] = [];

            $field['warning'][] = $message;
        }
    }

    public function isValid() {
        return $this->info['valid'];
    }


    private function field_Add($fieldName, &$fieldValue) {
        $this->info['fields'][$fieldName] = [
            'valid' => true,
            'value' => &$fieldValue,
            'state' => '',
            'errors' => [],
            'warnings' => [],
            'successes' => []
        ];

        return $this->info['fields'][$fieldName];
    }

    private function field_Exists($fieldName) {
        return array_key_exists($fieldName, $this->info['fields']);
    }

    // private function field_Message($type, $prioroty, $message = null) {

    // }

    private function &field_Get($fieldName) {
        if (!isset($this->info['fields'][$fieldName]))
            throw new \Exception("Field `{$fieldName}` does not exist.");

        return $this->info['fields'][$fieldName];
    }

}
