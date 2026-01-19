<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Forms;
use EC\Text\HText;

class VEmail extends Forms\VField {

    public function __construct($args = []) {
        parent::__construct($args, [
            'required' => true
        ]);
    }

    protected function _validate(&$value) {
        $args = $this->getArgs();

        if ($value === '') {
            if ($args['required'])
                $this->error(HText::_('Forms:fields.notSet'));

            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL))
			$this->error(HText::_('Forms:fields.email_WrongFormat'));
    }

}
