<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Text\HText;

class VInt extends Forms\VField {
    public function __construct($args = []) {
        parent::__construct($args, [
            'required' => true,
            'minValue' => null,
            'maxValue' => null
        ]);
    }

    protected function _validate(&$value) {
        $args = $this->getArgs();

        if ($value === '') {
            if ($args['required'])
                $this->error(HText::_('Forms:fields.notSet'));

            return;
        }
        if (!is_numeric($value))
            $this->error(HText::_('Forms:fields.int_NotANumber'));
        else {
            $value = intval($value);
            if (!is_int($value))
                $this->error(HText::_('Forms:fields.int_NotAnInt'));
            else {
                if ($args['minValue'] !== null) {
                    if ($value < $args['minValue']) {
                        $this->error(HText::_(
                                'Forms:fields.int_BelowMin', [ $args['minValue'] ]));
                    }
                }

                if ($args['maxValue']) {
                    if ($value > $args['maxValue']) {
                        $this->error(HText::_(
                                'Forms:fields.int_AboveMax', [ $args['maxValue'] ]));
                    }
                }
            }
        }
    }

}
