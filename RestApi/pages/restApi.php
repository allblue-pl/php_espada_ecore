<?php namespace EC\RestApi;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

$site = new SRestApi();

$restApiClass = E\Args::Page('restApi');
if (E\Args::Page_Exists('args'))
    $apiArgs = E\Args::Page('args');
else
    $apiArgs = [];

if (!class_exists($restApiClass))
    throw new \Exception("RestApi `{$restApiClass}` does not exist.");

$site->restApi(new $restApiClass($site, $apiArgs));

\Espada::Initialize($site);
