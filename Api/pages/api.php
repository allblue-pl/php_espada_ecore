<?php
defined('_ESPADA') or die(NO_ACCESS);


$site = new EC\SApi();

$api_class = E\Args::Page('api');
if (E\Args::Page_Exists('args'))
    $api_args = E\Args::Page('args');
else
    $api_args = [];

if (!class_exists($api_class))
    throw new \Exception("Api `{$api_class}` does not exist.");

$site->api(new $api_class($site, $api_args));


Espada::Initialize($site);
