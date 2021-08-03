<?php namespace EC\Router;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HRouter
{

    static public function GetAlias(string $str)
    {
        $str = trim(mb_strtolower($str));
        $str = EC\HStrings::EscapeLangCharacters($str);
        $str = str_replace(' ', '-', $str);
        $str = EC\HStrings::RemoveCharacters($str,
                'qwertyuiopasdfghjklzxcvbnm0123456789-');
        $str = EC\HStrings::RemoveDoubles($str, '-');

        return $str;
    }

}