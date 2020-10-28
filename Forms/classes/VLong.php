<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VLong extends Forms\VField
{

    private $min = 0;
    private $max = 0;
    private $required = true;

    public function __construct($args = [])
    {
        parent::__construct($args, [
            'required' => true,
            'minValue' => null,
            'maxValue' => null
        ]);
    }

    protected function _validate(&$value)
    {
        $args = $this->getArgs();

        if ($value === '') {
            if ($args['required'])
                $this->error(EC\HText::_('Forms:fields.notSet'));

            return;
        }

        if (!$args['required'] && $value === '')
            return;

        if (!is_numeric($value))
            $this->error(EC\Text\HText::_('Forms:fields.long_NotANumber'));
        else {
            $number = $value + 0;
            if (fmod($number, 1) !== 0.0)
                $this->error(EC\Text\HText::_('Forms:fields.long_NotWhole'));
            else {
                if ($args['minValue'] !== null) {
                    if ($number < $args['minValue']) {
                        $this->error(EC\Text\HText::_(
                                'Forms:fields.int_BelowMin', array($this->min)));
                    }
                }

                if ($args['maxValue']) {
                    if ($number > $args['maxValue']) {
                        $this->error(EC\Text\HText::_(
                                'Forms:fields.int_AboveMax', array($this->max)));
                    }
                }
            }
        }
    }

}
