<?php namespace EC\ABWeb;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MABWeb extends E\Module {

    private EC\Basic\MHead $_header;
    private string $_dirPath;

    public function __construct(EC\Basic\MHead $header, $dirPath) {
        $this->_header = $header;
        $this->_dirPath = $dirPath;
    }

    
    protected function _preInitialize(E\Site $site) {
        if (!file_exists($this->_dirPath))
            throw new \Exception("'abWeb' path does not exist.");

        $headerPath = $this->_dirPath . '/header.html';
        if (file_exists($headerPath)) {
            $headerHtml = file_get_contents($headerPath);
            $headerHtml = str_replace("{{base}}", SITE_BASE, $headerHtml);

            $this->_header->addHtml($headerHtml);
        }
        $headerPath = $this->_dirPath . '/header_Scripts.html';
        if (file_exists($headerPath)) {
            $headerHtml = file_get_contents($headerPath);
            $headerHtml = str_replace("{{base}}", SITE_BASE, $headerHtml);

            $this->_header->addHtml($headerHtml);
        }

        $bodyPath = $this->_dirPath . '/postBody.html';
        if (file_exists($bodyPath)) {
            $bodyHtml = file_get_contents($bodyPath);
            $bodyHtml = str_replace("{{base}}", SITE_BASE, $bodyHtml);

            $site->addL('postBody', E\Layout::_('Basic:raw', [
                'raw' => $bodyHtml,
            ]));
        }

        $bodyPath = $this->_dirPath . '/postBody_Scripts.html';
        if (file_exists($bodyPath)) {
            $bodyHtml = file_get_contents($bodyPath);
            $bodyHtml = str_replace("{{base}}", SITE_BASE, $bodyHtml);

            $site->addL('postBody', E\Layout::_('Basic:raw', [
                'raw' => $bodyHtml,
            ]));
        }
    }

}