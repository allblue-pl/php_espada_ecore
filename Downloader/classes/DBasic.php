<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class DBasic extends DDownloader {
    public function __construct(EC\SDownloader $site, array $requiredPermissions = [],
            $userType = 'Default') {
        parent::__construct($site);

        $site->addM('db', new EC\MDatabase());
        $site->addM('session', new EC\MSession($site->m->db));
        $site->addM('user', new EC\Users\MUser($site->m->session,
                $site->m->db, $userType));
    }
}
