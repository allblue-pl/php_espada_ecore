<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class DUser extends DDownloader {
    public function __construct(SUserDownloader $site) {
        parent::__construct($site);
    }
}