<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VDate extends Forms\VField
{

    private $texts = null;

    public function __construct($args = [])
    {
        parent::__construct($args, [
            'required' => true,
            'minDate' => null,
            'maxDate' => null
        ]);

        $this->texts = EC\HText::GetTranslations('Forms:fields');
    }

    protected function _validate(&$value)
    {
        $args = $this->getArgs();

        if ($value === null) {
            if ($args['required'])
                $this->error($this->texts->notSet);
            else
                return;
        } else {
            if (!is_numeric($value))
                $this->error($this->texts->date_WrongFormat);

            $value = intval($value);

            if ($args['minDate'] !== null) {
                if ($value < $args['minDate']) {
                    $this->error($this->texts->get('date_BelowMinDate',
                            array(date(EC\HText::_('Date:format_Date'),
                            $args['minDate']))));
                }
            }

            if ($args['maxDate'] !== null) {
                if ($value > $args['maxDate']) {
                    $this->error($this->texts->get('date_AboveMaxDate',
                            array(date(EC\HText::_('Date:format_Date'),
                            $args['maxDate']))));
                }
            }
        }
    }

}
