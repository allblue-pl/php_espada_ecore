<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Text\HText;

class VTime extends Forms\VField {

    private $texts = null;

    public function __construct($args = []) {
        parent::__construct($args, [
            'type' => 'dateTime',
            'required' => true,
            'minTime' => null,
            'maxTime' => null
        ]);

        $this->texts = HText::GetTranslations('Forms:fields');
    }

    protected function _validate(&$value) {
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

            if ($args['minTime'] !== null) {
                if ($value < $args['minTime']) {
                    $this->error($this->texts->get('date_BelowMinDate',
                            array(date(HText::_('Date:Format_Date'),
                            $args['minTime']))));
                }
            }

            if ($args['maxTime'] !== null) {
                if ($value > $args['maxTime']) {
                    $this->error($this->texts->get('date_AboveMaxDate',
                            array(date(HText::_('Date:Format_Date'),
                            $args['maxTime']))));
                }
            }
        }
    }

}
