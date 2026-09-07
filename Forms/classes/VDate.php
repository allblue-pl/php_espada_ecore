<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;
use EC\Text\HText;

class VDate extends Forms\VField {
    public function __construct($args = []) {
        parent::__construct($args, [
            'required' => true,
            'minDate' => null,
            'minDateError' => null,
            'maxDate' => null,
            'maxDateError' => null,
        ]);
    }

    protected function _validate(&$value) {
        $args = $this->getArgs();

        if ($value === null) {
            if ($args['required'])
                $this->error(HText::_("Date:NotSet"));
            else
                return;
        } else {
            if (!is_numeric($value))
                $this->error(HText::_("Date:Date_WrongFormat"));

            $value = intval($value);

            if ($args['minDate'] !== null) {
                if ($value < $args['minDate']) {
                    if ($args["minDateError"] !== null)
                        $this->error($args["minDateError"]);
                    else {
                        $this->error(HText::_('Date:Date_BelowMinDate',
                                array(date(HText::_('Date:Format_Date'),
                                $args['minDate']))));
                    }
                }
            }

            if ($args['maxDate'] !== null) {
                if ($value > $args['maxDate']) {
                    if ($args["maxDateError"] !== null)
                        $this->error($args["maxDateError"]);
                    else {
                        $this->error(HText::_('Date:Date_AboveMaxDate',
                                array(date(HText::_('Date:Format_Date'),
                                $args['maxDate']))));
                    }
                }
            }
        }
    }
}
