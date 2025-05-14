<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CFile {

    private $cache = null;

    private $id = null;
    private $userId = null;
    private $hash = null;


    public function __construct(MCache $cache, $id, $user_id, $hash)
    {
        $this->cache = $cache;

        $this->id = $id;
        $this->userId = $user_id;
        $this->hash = $hash;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFilePath()
    {
        return $this->cache->getFilePath($this->id, $this->hash);
    }

    public function release()
    {
        $this->cache->releaseFile($this->id, $this->userId, $this->hash);
    }

}
