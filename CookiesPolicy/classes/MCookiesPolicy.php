<?php namespace EC\CookiesPolicy;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MCookiesPolicy extends E\Module
{

    private $title = null;
    private $body = null;

    public function __construct()
    {

    }

    public function setContent($title, $body)
    {
        $this->title = $title;
        $this->body = $body;
    }


    protected function _preDisplay(E\Site $site)
    {
        parent::_preDisplay($site);

        if ($this->title === null || $this->body === null) {
            throw new \Exception('Cookies Policy content not set.');
        }

        $site->addL('postBody', E\Layout::_('CookiesPolicy:cookiesPolicy', [
            'Title' => $this->title,
            'Body' => $this->body,
        ]));
    }

}