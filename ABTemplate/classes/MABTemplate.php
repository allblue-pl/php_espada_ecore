<?php namespace EC\ABTemplate;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MABTemplate extends \E\Module {

    private $name = null;
    private $header = null;

    public function __construct(\EC\Basic\MHead $header, $name)
    {
        $this->header = $header;
        $this->name = $name;
    }

    public function getBuildPath()
    {
        return PATH_CACHE . "/ABTemplate/{$this->name}";
    }

    public function getBuildUri()
    {
        return URI_TMP . "ab-template/{$this->name}";
    }

    protected function _preInitialize(E\Site $site)
    {
        // if ($this->name === null)
        //     throw new \Exception('ABTemplate name not set.');

        $file_path = $this->getBuildPath() . '/header.html';

        if (!file_exists($file_path)) {
            E\Notice::Add('Cannot find ABTemplates `header.html`.');
            return;
        }

        $header_html = file_get_contents($file_path);
        $header_html = str_replace('{{base}}', SITE_BASE, $header_html);

        $this->header->addHtml($header_html);
    }

}
