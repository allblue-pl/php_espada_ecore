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
            $value = floatval($value);
            if (fmod($value, 1) !== 0.0)
                $this->error(HText::_('Forms:fields.long_NotWhole'));
            else {
                if ($args['minValue'] !== null) {
                    if ($value < $args['minValue']) {
                        $this->error(HText::_(
                                'Forms:fields.int_BelowMin', array($args['minValue'])));
                    }
                }

                if ($args['maxValue']) {
                    if ($value > $args['maxValue']) {
                        $this->error(HText::_(
                                'Forms:fields.int_AboveMax', array($args['maxValue'])));
                    }
                }
            }
        }
    }

}
