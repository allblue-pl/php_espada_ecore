<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class SDownloader extends E\Site
{

    private $downloader = null;
    private $downloadName = null;

    public function __construct()
    {
        parent::__construct();

        $this->setRootL(E\Layout::_('Basic:raw', [ 'raw' => '' ]));

        $uri_args = E\Args::Uri('_extra');
        if (count($uri_args) === 0)
            throw new \Exception('Download type not set: ' . E\Uri::Current());

        $this->downloadName = $uri_args[0];
    }

    public function setDownloader(DDownloader $downloader)
    {
        $this->downloader = $downloader;
    }

    private function outputDownload()
    {
        if ($this->downloader === null)
            return CResult::Failure('Downloader not set.');

        $this->downloader->outputDownload($this->downloadName);
    }

    /* E\Site Overrides */
    protected function _preDisplay()
    {
        parent::_preDisplay();

        $this->outputDownload();
    }

}
