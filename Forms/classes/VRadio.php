<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VRadio extends Forms\VField
{

    private $texts = null;

    public function __construct($args = [])
    {
        parent::__construct($args, [
            'required' => true,
            'values' => null
        ]);

        $this->texts = EC\Text\HText::GetTranslations('Forms:fields');
    }

    protected function _validate(&$value)
    {
        $args = $this->getArgs();

        if ($args['required'] && $value === '')
            $this->error($this->texts->notSet);

        if ($args['values'] !== null) {
            if (!in_array($value, $args['values']))
                $this->error($this->texts->radio_WrongValue);
        }
    }

}
