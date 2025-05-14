<?php namespace EC\CookiesPolicy;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MCookiesPolicy extends E\Module {

    private $head = null;
    private $title = null;
    private $body = null;
    private $scriptCSPHash = null;

    public function __construct(EC\Basic\MHead $head)
    {
        $this->head = $head;
    }

    public function setContent($title, $body)
    {
        $this->title = $title;
        $this->body = $body;
        $this->scriptCSPHash = $this->head->generateScriptCSPHash();
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

        $site->addL('postBodyInit', new EC\Basic\LScript(" 
            jsLibs.require('e-cookies-policy').init();
        ", $this->scriptCSPHash));
    }

}