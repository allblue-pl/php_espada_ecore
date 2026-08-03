<?php namespace EC\Text;
defined('_ESPADA') or die;

use E, EC;

class HText {

    static public ?\Closure $Listeners_OnTextNotFound = null;
	static private array $Translations = [];

	static public function _(string $text, array $args = []) {
		$textArray = self::ParseText($text);

		if ($textArray[0] === '' || $textArray[2] === '') {
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

            if (self::$Listeners_OnTextNotFound !== null)
                (self::$Listeners_OnTextNotFound)($translation);

            return '#' . $translation . '#';
        }

		$translations_key = self::GetTranslationsKey($textArray[0],
				$textArray[1]);

		self::Load($translations_key, $textArray[0], $textArray[1]);
		return self::$Translations[$translations_key]
			    ->get($textArray[2], $args);
	}

	static public function GetTranslations(string $path) {
		$pathArray = self::ParsePath($path);

		$translations_key = self::GetTranslationsKey($pathArray[0],
				$pathArray[1]);

		self::Load($translations_key, $pathArray[0], $pathArray[1]);
		return self::$Translations[$translations_key];
	}

	static public function GetTranslationsKey(string $package, ?string $file = '') {
		$key = $package;
		if ($file !== null)
			$key .= ':' . $file;

		return $key;
	}

	static public function Load(string $translationsKey, string $package, $path = '') {
		if (isset(self::$Translations[$translationsKey]))
			return;

		self::$Translations[$translationsKey] =
				new CTranslations($package, $path);
	}

    static public function SetListener_OnTextNotFound(\Closure $onTextNotFoundFn) {
        self::$Listeners_OnTextNotFound = $onTextNotFoundFn;
    }


	static private function ParseText(string $text) {
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

	static private function ParsePath(string $langPath) {
		$pos = mb_strpos($langPath, ':');
		if ($pos === false)
			return array($langPath, '');
		else {
			return array(
				mb_substr($langPath, 0, $pos),
				mb_substr($langPath, $pos + 1)
			);
		}
	}
}
