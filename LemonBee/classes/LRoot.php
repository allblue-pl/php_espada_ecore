<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LRoot extends E\Layout
{

    private $fields = null;

    public function __construct()
    {
        parent::__construct('LemonBee:index');

        $this->fields = [
            'showHeader' => true,
            'panelClass' => '',
            'images' => [
                'favicon' => E\Uri::File('LemonBee:images/favicon.ico'),
                'appleTouchIcon' => E\Uri::File(
                        'LemonBee:images/apple-touch-icon.png'),
            ],
            'layout' => null
        ];
    }

    public function getLayout()
    {
        return $this->fields['layout'];
    }

    public function setLayout_LogIn()
    {
        $this->fields['layout'] = 'logIn';
    }

    public function setLayout_Panel()
    {
        $this->fields['layout'] = 'panel';
    }

    public function setPanelClass($panel_class)
    {
        $this->fields['panelClass'] = $panel_class;
    }

    protected function _getFields()
    {
        if ($this->fields['layout'] === null)
            throw new \Exception('Layout not set.');

        return $this->fields;
    }

}
