<?php namespace EC\CookiesPolicy;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Basic\MHead;

class MCookiesPolicy extends E\Module {
    private MHead $head;
    private ?string $title = null;
    private ?string $body = null;
    private ?string $scriptCSPHash = null;

    public function __construct(E\Site $site, MHead $head) {
        parent::__construct($site);

        $this->head = $head;
    }

    public function setContent(string $title, string $body) {
        $this->title = $title;
        $this->body = $body;
        $this->scriptCSPHash = $this->head->generateScriptCSPHash();
    }


    protected function _preDisplay(E\Site $site): void {
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