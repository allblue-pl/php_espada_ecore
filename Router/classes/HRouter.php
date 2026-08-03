<?php namespace EC\Router;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Strings\HStrings;

class HRouter {
    static public function GetAlias(string $str) {
        $str = trim(mb_strtolower($str));
        $str = HStrings::EscapeLangCharacters($str);
        $str = str_replace(' ', '-', $str);
        $str = HStrings::RemoveCharacters($str, 'a-z0-9\\-');
        $str = HStrings::RemoveDoubles($str, '-');

        return $str;
    }
}