<?php namespace EC\Cache;
defined('_ESPADA') or die('NO_ACCESS');

use E, EC;

class CCacheFile {
    private string $id = '';
    private ?string $filePath = '';

    public function __construct(string $id, string $filePath) {
        $this->id = $id;
        $this->filePath = $filePath;
    }

    public function getId() {
        return $this->id;
    }

    public function getPath() {
        return $this->filePath;
    }

    public function release() {
        unlink($this->filePath);
        $this->filePath = null;
    }

}
