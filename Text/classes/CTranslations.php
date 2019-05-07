<?php namespace EC\Text;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CTranslations
{

	private $package = '';
	private $path = '';
	private $translations = [];
	private $noFile = true;

	/**
	 *
	 * @param string $package
	 */
	public function __construct($package, $path = '')
	{
		$lang_name = \E\Langs::Get()['name'];

		$this->package = $package;
		$this->path = $lang_name;
		if ($path !== '')
			$this->path .= '.' . $path;

		$t_path = 'languages/' . $this->path . '.ini';

		$file_path = E\Package::Path($package, $t_path);
		if ($file_path !== null)
			$this->translations = parse_ini_file($file_path);
	}

	/**
	 *
	 * @param string $text
	 * @return string
	 */
	public function get($text, $args = [])
	{
		$args_length = count($args);

		if (isset($this->translations[$text])) {
			$translation = $this->translations[$text];

			foreach ($args as $argName => $argValue)
				$translation = str_replace("{{$argName}}", $argValue, $translation);

			return $translation;
		}

		$translation = $this->package . ':' . $this->path . '.' . $text;
		if ($args_length > 0)
			$translation .= '(' . implode(', ', $args) . ')';

		return '#' . $translation . '#';
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	public function getArray()
	{
		return $this->translations;
	}

}
