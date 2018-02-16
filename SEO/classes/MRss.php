<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MRss extends E\Module
{

    private $info = null;

    public function __construct(EC\Basic\MHeader $header, $is_home)
    {
        parent::__construct();

        $this->info = [
            'rel' => $is_home ? 'alternate' : 'home',
            'type' => 'application/rss+xml',
            'href' => HRss::GetFileUri()
        ];

        $feed_file_path = HRss::GetFilePath();

        if (file_exists($feed_file_path)) {
            $header->addTag('link', $this->info, true);
        }
    }

    public function getInfo()
    {
        return $this->info;
    }

}
