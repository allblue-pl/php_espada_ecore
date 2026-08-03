<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Text\HText;

class VLong extends Forms\VField {
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
            $this->error(HText::_('Forms:fields.long_NotANumber'));
        else {
            $number = $value + 0;
            if (fmod($number, 1) !== 0.0)
                $this->error(HText::_('Forms:fields.long_NotWhole'));
            else {
                if ($args['minValue'] !== null) {
                    if ($number < $args['minValue']) {
                        $this->error(HText::_(
                                'Forms:fields.int_BelowMin', array($args['minValue'])));
                    }
                }

                if ($args['maxValue']) {
                    if ($number > $args['maxValue']) {
                        $this->error(HText::_(
                                'Forms:fields.int_AboveMax', array($args['maxValue'])));
                    }
                }
            }
        }
    }

}
