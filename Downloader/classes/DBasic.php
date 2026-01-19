<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Session\MSession;
use EC\Users\MUser;

class DBasic extends DDownloader {
    public function __construct(SDownloader $site, array $requiredPermissions = [],
            $userType = 'Default') {
        parent::__construct($site);

        $site->addM('db', new MDatabase());
        $site->addM('session', new MSession($site->m->db));
        $site->addM('user', new MUser($site->m->session,
                $site->m->db, $userType));
    }
}
