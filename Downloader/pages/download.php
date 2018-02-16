<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


$site = new SDownloader();

$downloader_class = E\Args::Page('downloader');

if (!class_exists($downloader_class))
    throw new \Exception("Downloader `{$downloader_class}` does not exist.");

$site->setDownloader(new $downloader_class($site));


\Espada::Initialize($site);
