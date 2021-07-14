<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MRss extends E\Module
{

    private $header = null;
    private $info = null;

    public function __construct(EC\Basic\MHead $header, $isHome = true)
    {
        parent::__construct();

        $this->header = $header;

        $this->info = [
            'rel' => true,
            'type' => 'application/rss+xml',
            'href' => HRss::GetFileUri()
        ];
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setHome($isHome)
    {   
        $this->info['rel'] = $isHome ? 'alternate' : 'home';
    }


    protected function _postInitialize(E\Site $site)
    {
        $feed_file_path = HRss::GetFilePath();

        if (file_exists($feed_file_path))
            $this->header->addTag('link', $this->info, true);
        else
            $this->header->addHTML('<!-- RSS file does not exist. -->');
    }

}
