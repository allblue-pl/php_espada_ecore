<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VFile extends Forms\VField {

    private $texts;

    public function __construct($args = []) {
        parent::__construct($args, [
            'required' => true
        ]);

        $this->texts = EC\Text\HText::GetTranslations('Forms:fields');
    }

    protected function _validate(&$value) {
        $args = $this->getArgs();

        if ($args['required'] && $value === '') {
            $this->error($this->texts->notSet);
            return;
        }
    }

}
