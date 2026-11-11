<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Session\MSession;
use EC\Users\MUser;

class SUserApi extends SDBApi {
    private MUser $user;
    private MSession $session;


    public function __construct(int $userType = 0) {
        parent::__construct();

        $this->session = new MSession($this, $this->getDB());
        $this->user = new MUser($this, $this->session, $this->getDB(), $userType);
    }

    public function getSession(): MSession {
        return $this->session;
    }

    public function getUser(): MUser {
        return $this->user;
    }
}