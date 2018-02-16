<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CPanel extends CPanelBase
{

    const COLOR_ORANGE = "orange";
    const COLOR_GREEN = "green";
    const COLOR_VIOLET = "violet";
    const COLOR_RED = "red";


    private $name = '';
    private $title = '';
    private $color = '';

    private $link = '/';

    private $subpanels = [];

    public function __construct($name, $page_name, $title,
                                $color = CPanel::COLOR_ORANGE,
                                $required_permissions = '')
    {
        parent::__construct($required_permissions);

        $this->name = $name;
        $this->title = $title;
        $this->color = $color;

        $this->link = E\Uri::Page($page_name);
        if ($this->link === null)
            E\Notice::Add("Page `{$page_name}` does not exist.");
    }

    public function add(CSubpanel $subpanel)
    {
        $this->subpanels[] = $subpanel;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getClass()
    {
        return $this->color;
    }

    public function getSubpanels()
    {
        return $this->subpanels;
    }

}
