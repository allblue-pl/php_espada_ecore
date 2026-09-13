<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Text\HText;

abstract class VField {
    private $args = null;

    private $validator = null;
    private $name = null;

    
    public function __construct($args, $defaultArgs) {
        $defaultArgs['notNull'] = true;
        
        foreach ($args as $argName => $argValue) {
            if (!array_key_exists($argName, $defaultArgs)) 
                throw new \Exception("Arg '{$argName}' does not exist.");

            $defaultArgs[$argName] = $args[$argName];
        }

        $this->args = $defaultArgs;
    }

    public function error($message = null) {
        $this->validator->fieldError($this->name, $message);
    }

    public function getArgs() {
        return $this->args;
    }

    public function success($message = null) {
        $this->validator->fieldSuccess($this->name, $message);
    }

    public function validate(CValidator $validator, $name, &$value) {
        $this->validator = $validator;
        $this->name = $name;

        if ($value === null) {
            if ($this->args['notNull']) {
                if ($this->args['required'])
                    $this->error(HText::_('Forms:fields.notSet'));
                else
                    $this->error(HText::_('Forms:fields.notNull'));
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
