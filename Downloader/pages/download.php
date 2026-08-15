<?php namespace EC\Downloader;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


$siteClass = E\Args::Page('site');
if (!class_exists($siteClass))
    throw new \Exception("Downloader site `{$siteClass}` does not exist.");

$site = new $siteClass();
if (!($site instanceof SDownloader))
    throw new \Exception("'site' must be a child of 'EC\Downloader\SDownloader'.");

$downloaderClass = E\Args::Page('downloader');

if (!class_exists($downloaderClass))
    throw new \Exception("Downloader `{$downloaderClass}` does not exist.");

$site->setDownloader(new $downloaderClass($site));


\Espada::Initialize($site);
