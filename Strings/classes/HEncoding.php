<?php namespace EC\Strings;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HEncoding
{

    static public function Convert($str, $to_encoding, $from_encoding)
    {
        $from_encoding = mb_strtolower($from_encoding);
        $to_encoding = mb_strtolower($to_encoding);

        if ($from_encoding === 'cp1250') {
            $str = self::CP1250_To_UTF8($str);
            $from_encoding = 'utf-8';

            if ($to_encoding === $from_encoding)
                return $str;
        } else if ($from_encoding === 'windows-1250')
            return iconv($from_encoding, $to_encoding, $str);

        return mb_convert_encoding($str, $to_encoding, $from_encoding);
    }


    static private function CP1250_To_UTF8($str)
    {
        $map = [
            chr(0x8A) => chr(0xA9),
            chr(0x8C) => chr(0xA6),
            chr(0x8D) => chr(0xAB),
            chr(0x8E) => chr(0xAE),
            chr(0x8F) => chr(0xAC),
            chr(0x9C) => chr(0xB6),
            chr(0x9D) => chr(0xBB),
            chr(0xA1) => chr(0xB7),
            chr(0xA5) => chr(0xA1),
            chr(0xBC) => chr(0xA5),
            chr(0x9F) => chr(0xBC),
            chr(0xB9) => chr(0xB1),
            chr(0x9A) => chr(0xB9),
            chr(0xBE) => chr(0xB5),
            chr(0x9E) => chr(0xBE),
            chr(0x80) => '&euro;',
            chr(0x82) => '&sbquo;',
            chr(0x84) => '&bdquo;',
            chr(0x85) => '&hellip;',
            chr(0x86) => '&dagger;',
            chr(0x87) => '&Dagger;',
            chr(0x89) => '&permil;',
            chr(0x8B) => '&lsaquo;',
            chr(0x91) => '&lsquo;',
            chr(0x92) => '&rsquo;',
            chr(0x93) => '&ldquo;',
            chr(0x94) => '&rdquo;',
            chr(0x95) => '&bull;',
            chr(0x96) => '&ndash;',
            chr(0x97) => '&mdash;',
            chr(0x99) => '&trade;',
            chr(0x9B) => '&rsquo;',
            chr(0xA6) => '&brvbar;',
            chr(0xA9) => '&copy;',
            chr(0xAB) => '&laquo;',
            chr(0xAE) => '&reg;',
            chr(0xB1) => '&plusmn;',
            chr(0xB5) => '&micro;',
            chr(0xB6) => '&para;',
            chr(0xB7) => '&middot;',
            chr(0xBB) => '&raquo;',
        ];

        return html_entity_decode(mb_convert_encoding(strtr($str, $map), 'UTF-8',
                'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
    }

}
