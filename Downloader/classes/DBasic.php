<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class DBasic extends DDownloader
{

    public function __construct(EC\SDownloader $site)
    {
        parent::__construct($site);

        $site->addM('session', new EC\MSession());
        $site->addM('db', new EC\MDatabase());
        $site->addM('user', new EC\Users\MUser($site->m->session,
                $site->m->db));
    }

}
