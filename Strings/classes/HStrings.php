<?php namespace EC\Strings;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HStrings
{

    static public $CharacterTypes = [ 'digits', 'letters', 'special' ];


    static public function GetCharsRegexp($types = [], $extra = '',
            $langs = null)
    {
        foreach ($types as $type) {
            if (!in_array($type, $types))
                throw new Exception("Unknown chars type `{$type}`.");
        }

        $chars = '';

        if (in_array('digits', $types))
            $chars .= '0-9';
        if (in_array('letters', $types))
            $chars .= 'a-zA-Z' . self::GetLangsSpecialCharacters();
        if (in_array('special', $types)) {
            $chars .= ' `!@#%&_=/<>:;",\'' .
                '\\\\' . '\\^' . '\\$' . '\\.' . '\\[' .'\\]' . '\\|' .
                '\\(' . '\\)' . '\\?' . '\\*' . '\\+' . '\\{' . '\\}' .
                '\\-';
        }

        return $chars . self::EscapeRegexpChars($extra);
    }

    static public function GetCharsRegexp_Basic($extra = '')
    {
        return self::GetCharsRegexp([ 'digits', 'letters', 'special' ], $extra);
    }

    static public function GetLangsSpecialCharacters($langs = null)
    {
        if ($langs === null)
            $langs = [ 'pl' ];

        $chars = '';
        if (in_array('pl', $langs))
            $chars .= 'ąćęłńóśźż' . 'ĄĆĘŁŃÓŚŹŻ';

        return $chars;
    }

    static public function EscapeLangCharacters($string, $langs = [])
    {
        $replace_from   = ['ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
                           'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż'];
        $replace_to     = ['a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z',
                           'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z'];

        return str_replace($replace_from, $replace_to, $string);
    }

    static public function EscapeRegexpChars($string)
    {
        $replace_from = [ '\\', '^', '$', '.', '[' .']', '|', '(' .
                ')', '?', '*', '+', '{', '}', '-', '#' ];
        $replace_to = [ '\\\\', '\\^', '\\$', '\\.', '\\[' .'\\]', '\\|', '\\(' .
                '\\)', '\\?', '\\*', '\\+', '\\{', '\\}', '\\-', '\\#' ];

        return str_replace($replace_from, $replace_to, $string);
    }

    // static public function RemoveCharacters_ByString($string, $allowed_characters_string)
    // {
    //     $new_str = '';
    //     for ($i = 0; $i < mb_strlen($string); $i++) {
    //         if (mb_strpos($allowed_characters_string, $string[$i]) > -1)
    //             $new_str .= (string)$string[$i];
    //     }

    //     return $new_str;
    // }

    static public function RemoveCharacters($string, $allowedRegexp)
    {
        $allowedRegexp = str_replace('#', '\\#', $allowedRegexp);

        $new_str = '';
        for ($i = 0; $i < mb_strlen($string); $i++) {
            if (preg_match('#[' . $allowedRegexp . ']#', $string[$i])) {
                $new_str .= (string)$string[$i];
            }
        }

        return $new_str;
    }

    static public function RemoveDoubles($string, $char)
    {
        while (true) {
            $string = str_replace($char.$char, $char, $string, $count);
            if ($count === 0)
                return $string;
        }
    }

    static public function ValidateChars($string, $validChars, &$invalidChars)
    {
        $regexp = '[^' . $validChars . ']';

        $invalidChars = [];

        mb_ereg_search_init($string, $regexp);
        while(true) {
            if (mb_ereg_search() === false)
                break;
            
            $match = mb_ereg_search_getregs();
            if (!in_array($match[0], $invalidChars))
                $invalidChars[] = $match[0];
        }
        
        if (count($invalidChars) === 0)
            return true;

        return false;
    }

}
