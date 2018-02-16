<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class DDownloader
{

    private $site = null;
    private $downloads = [];

    public function __construct(EC\SDownloader $site)
    {
        $this->site = $site;
    }

    public function outputdownload($download_name)
    {
        if (!array_key_exists($download_name, $this->downloads))
            throw new \Exception("Download `{$download_name}` does not exist.");

        $download = $this->downloads[$download_name];

        try {
            call_user_func([$this, $download['fn']]);
        } catch (\Exception $e) {
            if (!EDEBUG)
                return CResult::Error(INTERNAL_ERROR_MESSAGE);

            throw $e;
        }
    }

    protected function download($name, $fn)
    {
        if (!method_exists($this, $fn))
            throw new \Exception("Action method `$fn` does not exist.");

        $this->downloads[$name] = [
            'fn' => $fn
        ];
    }

    protected function getSite()
    {
        return $this->site;
    }

}
