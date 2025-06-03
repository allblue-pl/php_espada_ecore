<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

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
                $this->error(EC\HText::_('Forms:fields.notSet'));

            return;
        }

        if (!$args['required'] && $value === '')
            return;

        if (!is_numeric($value))
            $this->error(EC\Text\HText::_('Forms:fields.int_NotANumber'));
        else {
            $number = $value + 0;
            if (!is_int($number))
                $this->error(EC\Text\HText::_('Forms:fields.int_NotAnInt'));
            else {
                if ($args['minValue'] !== null) {
                    if ($number < $args['minValue']) {
                        $this->error(EC\Text\HText::_(
                                'Forms:fields.int_BelowMin', [ $args['minValue'] ]));
                    }
                }

                if ($args['maxValue']) {
                    if ($number > $args['maxValue']) {
                        $this->error(EC\Text\HText::_(
                                'Forms:fields.int_AboveMax', [ $args['maxValue'] ]));
                    }
                }
            }
        }
    }

}
