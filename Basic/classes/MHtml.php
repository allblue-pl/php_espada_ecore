<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MHtml extends E\Module {

    const OUTPUT_HTML = 0;

    private $name = null;
	private $fields = null;

	public function __construct($name = null) {
        parent::__construct();

		$this->name = $name;
        $this->fields = [];
	}

    public function setField($name, $value) {
        $this->fields[$name] = $value;
    }

	protected function output_Default(E\Fields &$fields) {
		foreach ($this->fields as $name => $value)
            $fields->set($name, $value);

		if ($this->name !== null) {
			if (E\File::Exists(PATH_DATA . '/html/' . $this->name . '.php'))
				require(PATH_DATA . '/html/' . $this->name . '.php');
			else if ($this->fields === null)
				throw new \Exception('Html data file `' .
								  PATH_DATA . '/html/' . $this->name . '.php' .
								  '` does not exist.');
		}
	}

}
