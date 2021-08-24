<?php namespace EC\Forms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Forms;

class VText extends Forms\VField
{

    private $texts = null;

    public function __construct($args = [])
    {
        parent::__construct($args, [
            'required' => true,
            'minLength' => null,
            'maxLength' => null,
            'regexp' => null,
            'trim' => false,
            'chars' => EC\HStrings::GetCharsRegexp_Basic('\r\n')
        ]);

        $this->texts = EC\Text\HText::GetTranslations('Forms:fields');
    }

    protected function _validate(&$value)
    {
        $args = $this->getArgs();

        if ($args['trim'])
            $value = trim($value);

        if ($value === '') {
            if ($args['required'])
                $this->error($this->texts->notSet);

            return;
        } else {
            if ($args['minLength'] !== null) {
                if (mb_strlen($value) < $args['minLength']) {
                    $this->error($this->texts->get(
                            'text_BelowMinLength', [$args['minLength']]));
                }
            }

            if ($args['maxLength'] !== null) {
                if ($args['maxLength'] > 0)
                    if (mb_strlen($value) > $args['maxLength'])
                        $this->error($this->texts->get(
                            'text_AboveMaxLength', [$args['maxLength']]));
            }

            if ($args['regexp'] !== null) {
                $regexp = str_replace('#', '\\#', $args['regexp'][0]);
                if (!preg_match("#{$regexp}#", $value)) {
                    $this->error($this->texts->get('text_WrongFormat',
                        [$args['regexp'][1]]));
                }
            }

            if ($args['chars'] !== null) {
                $chars = str_replace('#', '\\#', $args['chars']);
                // $value = ' hello ';
                // echo '#' . $chars . '#' . $value . '#';
                $invalidChars = [];
                if (!EC\HStrings::ValidateChars($value, $chars, $invalidChars)) {

                    $not_allowed_chars_arr = $invalidChars;
                    $not_allowed_chars = implode(', ', $not_allowed_chars_arr);

                    $not_allowed_chars = str_replace('\\\\', '&#92;', $not_allowed_chars);
                    $not_allowed_chars = str_replace('\\', '', $not_allowed_chars);
                    $not_allowed_chars = str_replace('&#92;', '\\', $not_allowed_chars);

                    $this->error($this->texts->get('text_NotAllowedCharacters',
                        [ $not_allowed_chars ]));

                }
            }
        }
    }

}
