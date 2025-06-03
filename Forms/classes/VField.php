<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

abstract class VField {

    private $args = null;

    private $validator = null;
    private $name = null;

    
    public function __construct($args, $default_args) {
        $default_args['notNull'] = true;

        foreach ($args as $a_name => $a) {
            if (!array_key_exists($a_name, $default_args))
                throw new \Exception("Arg `{$a_name}` does not exist.");

            $default_args[$a_name] = $args[$a_name];
        }

        $this->args = $default_args;
    }

    public function error($message = null) {
        $this->validator->fieldError($this->name, $message);
    }

    public function getArgs() {
        return $this->args;
    }

    public function getInfo() {
        return $this->info;
    }

    public function success($message = null) {
        $this->validator->fieldSuccess($this->name, $message);
    }

    public function validate(CValidator $validator, $name, $value) {
        $this->validator = $validator;
        $this->name = $name;

        if ($value === null) {
            if ($this->args['notNull']) {
                if ($this->args['required'])
                    $this->error(EC\HText::_('Forms:fields.notSet'));
                else
                    $this->error(EC\HText::_('Forms:fields.notNull'));
            }


        } else
            $this->_validate($value);

        $this->name = null;
        $this->validator = null;
    }

    public function warning($message = null) {
        $this->validator->fieldWarning($this->name, $message);
    }

    abstract protected function _validate(&$value);

}
