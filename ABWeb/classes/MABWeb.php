<?php namespace EC\ABWeb;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MABWeb extends E\Module
{

    public function __construct(EC\Basic\MHeader $header, $dirPath)
    {
        $this->_header = $header;
        $this->_dirPath = $dirPath;
    }

    
    protected function _preInitialize(E\Site $site)
    {
        if (!file_exists($this->_dirPath))
            throw new \Exception("'abWeb' path does not exist.");

        $headerPath = $this->_dirPath . '/header.html';
        if (file_exists($headerPath)) {
            $headerHtml = file_get_contents($headerPath);
            $headerHtml = str_replace("{{base}}", SITE_BASE, $headerHtml);

            $this->_header->addHtml($headerHtml);
        }
    }

}