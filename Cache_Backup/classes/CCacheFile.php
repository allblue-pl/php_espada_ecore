<?php namespace EC\Cache;
defined('_ESPADA') or die('NO_ACCESS');

use E, EC;

class CCacheFile
{

    private $cache = null;
    private $id = '';
    private $filePath = '';

    public function __construct(MCache $cache,
                                $id, $file_path, $expires = 0)
    {
        $this->cache = $cache;
        $this->id = $id;
        $this->filePath = $file_path;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPath()
    {
        return $this->filePath;
    }

    public function release()
    {
        unlink($this->filePath);
        $this->filePath = null;
    }

}
