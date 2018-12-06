<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

if (!E\Args::Page_Exists('siteClass'))
    throw new \Exception("Page arg 'siteClass' not set.");

$siteClass = E\Args::Page('siteClass');
$site = new $siteClass();

\Espada::Initialize($site);