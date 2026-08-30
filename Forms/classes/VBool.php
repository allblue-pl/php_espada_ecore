<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Text\HText;

class VBool extends Forms\VField {
    public function __construct($args = []) {
        parent::__construct($args, [
            'required' => false,
        ]);
    }

    protected function _validate(&$value) {
        $args = $this->getArgs();

        if (!$value) {
            if ($args['required'])
                $this->error(HText::_("Forms:fields.notChecked"));

            return;
        }
    }

}
