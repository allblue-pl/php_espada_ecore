<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MFacebook extends E\Module
{

    private $header = null;
    private $version = null;

    public function __construct(EC\Basic\MHeader $header, $version = 'v3.2')
    {
        parent::__construct();

        $this->header = $header;
        $this->version = $version;
    }

    public function og_AddTag($name, $value)
    {
        $this->header->addTag('meta', [
            'property' => $name,
            'content' => $value
        ], true);
    }


    protected function _postInitialize(E\Site $site)
    {
        $appId = EC\HConfig::GetRequired('Facebook', 'appId');

        $this->og_AddTag('og:app_id', $appId);
        $this->og_AddTag('og:type', 'article');

        $l_init = $site->addL('init', E\Layout::_('Facebook:init', [
            'appId' => $appId,
            'version' => $this->version,
            'langCode' => str_replace('-', '_', E\Langs::Get()['code']),
        ]));
    }

}
