<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Text\HText;

class VFloat extends Forms\VField {
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
            $this->error(EC\Text\HText::_('Forms:fields.int_NotANumber'));
        else {
            $value = floatval($value);
            if ($args['minValue'] !== null) {
                if ($value < $args['minValue']) {
                    $this->error(EC\Text\HText::_(
                            'Forms:fields.int_BelowMin', array($args['minValue'])));
                }
            }

            if ($args['maxValue']) {
                if ($value > $args['maxValue']) {
                    $this->error(EC\Text\HText::_(
                            'Forms:fields.int_AboveMax', array($args['maxValue'])));
                }
            }
        }
    }

}
