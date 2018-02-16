<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


$site_class = E\Args::Page('site');
if (!class_exists($site_class))
    throw new \Exception("SLemonBee child `{$site_class}` does not exist.");

$site = new $site_class();

$panel_class = E\Args::Page('panel');
if (!class_exists($panel_class))
    throw new \Exception("LemonBee panel `{$panel_class}` does not exist.");

$site->lbSetPage_Panel(new $panel_class($site));


\Espada::Initialize($site);
