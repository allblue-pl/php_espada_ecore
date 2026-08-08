<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Api\CResult;

class SDownloader extends E\Site {

    private $downloader = null;
    private $downloadName = null;

    public function __construct() {
        parent::__construct();

        $this->setRootL(E\Layout::_('Basic:raw', [ 'raw' => '' ]));

        $uriArgs = E\Args::Uri_Extra();
        if (count($uriArgs) === 0)
            throw new \Exception('Download type not set: ' . E\Uri::Current());

        $this->downloadName = $uriArgs[0];
    }

    public function setDownloader(DDownloader $downloader) {
        $this->downloader = $downloader;
    }

    private function outputDownload() {
        if ($this->downloader === null)
            return CResult::Failure('Downloader not set.');

        $this->downloader->outputDownload($this->downloadName);
    }

    /* E\Site Overrides */
    protected function _preDisplay(): void {
        parent::_preDisplay();

        $this->outputDownload();
    }

}
