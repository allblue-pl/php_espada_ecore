<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Strings\HStrings;
use EC\Text\HText;

class VEnum extends Forms\VField {
    public function __construct($args = []) {
        parent::__construct($args, [
            'required' => true,
            'values' => [],
        ]);
    }

    protected function _validate(&$value) {
        $args = $this->getArgs();

        if ($value === '') {
            if ($args['required'])
                $this->error(HText::_("Forms:fields.notSet"));

            return;
        } else {
            if (!in_array($value, $args["values"])) {
                $this->error(HText::_("Forms:fields.notInValues", 
                        [ implode(", ", $args["values"]) ]));
            }
        }
    }

}
