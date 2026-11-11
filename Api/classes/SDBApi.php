<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Session\MSession;
use EC\Users\MUser;

class SDBApi extends SApi {
    private MDatabase $db;


    public function __construct() {
        parent::__construct();

        $this->db = new MDatabase($this);
    }

    public function getDB(): MDatabase {
        return $this->db;
    }
}