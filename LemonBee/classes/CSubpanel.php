<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CSubpanel extends CPanelBase
{

    private $name = '';
    private $alias = '';
    private $title = '';
    private $faIcon = '';

    private $link = '/';

    public function __construct($name, $page_name, $alias, $title, $fa_icon,
                                $required_permissions = '')
    {
        parent::__construct($required_permissions);

        $this->name = $name;
        $this->alias = $alias;
        $this->title = $title;
        $this->faIcon = $fa_icon;

        $this->link = E\Uri::Page($page_name);
        if ($this->link === null)
            E\Notice::Add("Page `{$page_name}` does not exist.");
        if ($alias !== '')
            $this->link .= $alias;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getFAIcon()
    {
        return $this->faIcon;
    }

}
