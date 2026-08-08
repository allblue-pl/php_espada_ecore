<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MHtml extends E\Module {

    const OUTPUT_HTML = 0;

    private ?string $name;
	private array $fields;

	public function __construct(E\Site $site, ?string $name = null) {
        parent::__construct($site);

		$this->name = $name;
        $this->fields = [];
	}

    public function setField(string $name, mixed $value) {
        $this->fields[$name] = $value;
    }

	protected function output_Default(E\Fields &$fields) {
        $fields->_set($this->fields);

		if ($this->name !== null) {
			if (E\File::Exists(PATH_DATA . '/html/' . $this->name . '.php'))
				require(PATH_DATA . '/html/' . $this->name . '.php');
			// else if ($this->fields === null)
			// 	throw new \Exception('Html data file `' .
			// 					  PATH_DATA . '/html/' . $this->name . '.php' .
			// 					  '` does not exist.');
		}
	}

}
