<?php namespace EC\Text;
defined('_ESPADA') or die;

use E, EC;

class HText
{

	static private $Translations = [];

	static public function _($text, $args = [])
	{
		$text_array = self::ParseText($text);

		if ($text_array[0] === '' || $text_array[2] === '') {
            $translation = $text;
            if (count($args) > 0) {
                $translation .= '(';
                $first = true;
                foreach ($args as $argName => $argValue) {
                    if (!$first)
                        $translation .= ', ';
                    else
                        $first = false;
    
                    $translation .= "$argName => $argValue";
                }
                $translation .= ')';
            }

            return '#' . $translation . '#';
        }

		$translations_key = self::GetTranslationsKey($text_array[0],
				$text_array[1]);

		self::Load($translations_key, $text_array[0], $text_array[1]);
		return self::$Translations[$translations_key]
			    ->get($text_array[2], $args);
	}

	static public function GetTranslations($path)
	{
		$path_array = self::ParsePath($path);

		$translations_key = self::GetTranslationsKey($path_array[0],
				$path_array[1]);

		self::Load($translations_key, $path_array[0], $path_array[1]);
		return self::$Translations[$translations_key];
	}

	static public function GetTranslationsKey($package, $file = '')
	{
		$key = $package;
		if ($file !== null)
			$key .= ':' . $file;

		return $key;
	}

	static public function Load($translations_key, $package, $path = '')
	{
		if (isset(self::$Translations[$translations_key]))
			return;

		self::$Translations[$translations_key] =
				new CTranslations($package, $path);
	}

	static private function ParseText($text)
	{
		$pos = mb_strrpos($text, ':');
		if ($pos === false)
			return ['', '', ''];

		$package = mb_substr($text, 0, $pos);
		$text = mb_substr($text, $pos + 1);

		$pos = mb_strrpos($text, '.');
		if ($pos === false)
			return [$package, '', $text];
		else {
			return [
				$package,
				mb_substr($text, 0, $pos),
				mb_substr($text, $pos + 1)
			];
		}
	}

	static private function ParsePath($lang_path)
	{
		$pos = mb_strpos($lang_path, ':');
		if ($pos === false)
			return array($lang_path, '');
		else {
			return array(
				mb_substr($lang_path, 0, $pos),
				mb_substr($lang_path, $pos + 1)
			);
		}
	}

}
