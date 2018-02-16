<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


$site_class = E\Args::Page('site');
if (!class_exists($site_class))
    throw new \Exception("SLemonBee child `{$site_class}` does not exist.");

$site = new $site_class();
$site->lbSetRequiredPermissions([]);

$site->lbSetPage_LogOut();


\Espada::Initialize($site);
