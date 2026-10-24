<?php namespace EC\Strings;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HStrings {

    static public $CharacterTypes = [ 'digits', 'letters', 'special' ];


    static public function GetCharsRegexp($types = [], $extra = '',
            $langs = null) {
        foreach ($types as $type) {
            if (!in_array($type, [ "digits", "letters", "special", "emojis" ]))
                throw new \Exception("Unknown chars type `{$type}`.");
        }

        $chars = '';

        if (in_array('digits', $types))
            $chars .= '0-9';
        if (in_array('letters', $types))
            $chars .= 'a-zA-Z' . self::GetLangsSpecialCharacters();
        if (in_array('special', $types)) {
            $chars .= ' `!@#%&_=/<>:;",\'–' .
                '\\\\' . '\\^' . '\\$' . '\\.' . '\\[' .'\\]' . '\\|' .
                '\\(' . '\\)' . '\\?' . '\\*' . '\\+' . '\\{' . '\\}' .
                '\\-';
        }
        if (in_array('emojis', $types)) {
            $chars .= '\\x{1F600}-\\x{1F64F}'; // Emoticons
            $chars .= '\\x{1F300}-\\x{1F5FF}'; // Symbols & Pictographs
            $chars .= '\\x{1F680}-\\x{1F6FF}'; // Transport & Map Symbols
            $chars .= '\\x{1F900}-\\x{1F9FF}'; // Supplemental Symbols
            $chars .= '\\x{1FA70}-\\x{1FAFF}'; // Symbols & Pictographs Ext. A
            $chars .= '\\x{2600}-\\x{26FF}'; // Misc Symbols
            $chars .= '\\x{2700}-\\x{27BF}'; // Dingbats
        }

        return $chars . self::EscapeRegexpChars($extra);
    }

    static public function GetCharsRegexp_Basic($extra = '') {
        return self::GetCharsRegexp([ 'digits', 'letters', 'special' ], $extra);
    }

    static public function GetLangsSpecialCharacters($langs = null) {
        if ($langs === null)
            $langs = [ 'pl' ];

        $chars = '';
        if (in_array('pl', $langs))
            $chars .= 'ąćęłńóśźż' . 'ĄĆĘŁŃÓŚŹŻ';

        return $chars;
    }

    static public function EscapeLangCharacters($string, $langs = []) {
        $replace_from   = ['ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
                           'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż'];
        $replace_to     = ['a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z',
                           'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z'];

        return str_replace($replace_from, $replace_to, $string);
    }

    static public function EscapeRegexpLangCharacters($string, $langs =[]) {
        $replace_regexp_from = [ 'a', 'c', 'e', 'l', 'n', 'o', 's', 'z',
                           'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z' ];
        $replace_regexp_to = [ '(a|ą)', '(c|ć)', '(e|ę)', '(l|ł)', '(n|ń)', '(o|ó)', '(s|ś)', '(z|ź|ż)',
                            '(A|Ą)', '(C|Ć)', '(E|Ę)', '(L|Ł)', '(N|Ń)', '(O|Ó)', '(S|Ś)', '(Z|Ź|Ź)' ];

        // $replace_regexp_from = [];
        // $replace_regexp_to = [];
        // for ($i = 0; $i < count($replace_from); $i++) {
        //     $replace_regexp_from[] = $replace_from[$i];
        //     $replace_regexp_to[] = '(' . $replace_from[$i] . '|' .
        //             $replace_to[$i] . ')';
        // }
        // for ($i = 0; $i < count($replace_to); $i++) {
        //     $replace_regexp_from[] = $replace_to[$i];
        //     $replace_regexp_to[] = '(' . $replace_to[$i] . '|' .
        //             $replace_from[$i] . ')';
        // }

        return str_replace($replace_regexp_from, $replace_regexp_to, $string);
    }

    static public function EscapeRegexpChars($string) {
        $replace_from = [ '\\', '^', '$', '.', '[' .']', '|', '(' ,
                ')', '?', '*', '+', '{', '}', '-', '#' ];
        $replace_to = [ '\\\\', '\\^', '\\$', '\\.', '\\[' .'\\]', '\\|', '\\(' ,
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

    static public function RemoveCharacters($string, $allowedRegexp) {
        $allowedRegexp = str_replace('#', '\\#', $allowedRegexp);

        $new_str = '';
        for ($i = 0; $i < mb_strlen($string); $i++) {
            if (preg_match('#[' . $allowedRegexp . ']#u', mb_substr($string, $i, 1))) {
                $new_str .= (string)mb_substr($string, $i, 1);
            }
        }

        return $new_str;
    }

    static public function RemoveDoubles($string, $char) {
        while (true) {
            $string = str_replace($char.$char, $char, $string, $count);
            if ($count === 0)
                return $string;
        }
    }

    static public function ValidateChars($string, $validChars, &$invalidChars) {
        $regexp = '/[^' . str_replace("/", "\\/", $validChars) . ']/u';
        $invalidChars = [];

        if (preg_match_all($regexp, $string, $matches))
            $invalidChars = array_unique($matches[0]);
        
        if (count($invalidChars) === 0)
            return true;

        return false;
    }

}
