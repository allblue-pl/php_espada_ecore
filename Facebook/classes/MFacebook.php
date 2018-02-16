<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MFacebook extends E\Module
{

    private $og = null;
    private $appId = '';

    public function __construct(MOpenGraph $og)
    {
        parent::__construct();

        $this->og = $og;
    }

    protected function _postInitialize(E\Site $site)
    {
        $app_id = EC\HConfig::GetRequired('Facebook', 'appId');

        $this->og->addTag('og:app_id', $app_id);
        $this->og->addTag('og:type', 'article');

        $l_init = $site->addL('init', E\Layout::_('Facebook:init', [
            'appId' => $app_id,
            'langCode' => E\Langs::Get()['code']
        ]));
    }

}
