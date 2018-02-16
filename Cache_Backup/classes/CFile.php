<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CFile
{

    private $id = null;
    private $userId = null;
    private $hash = null;


    public function __construct($id, $user_id, $hash)
    {
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
        return MCache::Dir . "/{$this->id}-{$this->hash}.cache";
    }

    public function remove()
    {

    }

}
