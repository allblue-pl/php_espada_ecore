<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VBool extends Forms\VField
{

    private $texts = null;

    public function __construct($args = [])
    {
        parent::__construct($args, [
            'required' => false,
        ]);

        $this->texts = EC\Text\HText::GetTranslations('Forms:fields');
    }

    protected function _validate(&$value)
    {
        $args = $this->getArgs();

        if (!$value) {
            if ($args['required'])
                $this->error($this->texts->notChecked);

            return;
        }
    }

}
