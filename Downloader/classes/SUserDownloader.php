<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Downloader\SDownloader;
use EC\Session\MSession;
use EC\Users\MUser;

class SUserDownloader extends SDownloader {
    private MUser $user;
    private MSession $session;
    private MDatabase $db;


    public function __construct(string $userType = "Default") {
        parent::__construct();

        $this->db = new MDatabase($this);
        $this->session = new MSession($this, $this->db);
        $this->user = new MUser($this, $this->session, $this->db, $userType);
    }

    public function getDB(): MDatabase {
        return $this->db;
    }

    public function getSession(): MSession {
        return $this->session;
    }

    public function getUser(): MUser {
        return $this->user;
    }
}