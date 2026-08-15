<?php

use EC\Api\SApi;

defined('_ESPADA') or die(NO_ACCESS);

$siteClass = E\Args::Page('site');
if (!class_exists($siteClass))
    throw new \Exception("Api site `{$siteClass}` does not exist.");

$site = new $siteClass();
if (!($site instanceof SApi))
    throw new \Exception("'site' must be a child of 'EC\Api\SApi'.");

$apiClass = E\Args::Page('api');
if (E\Args::Page_Exists('args'))
    $apiArgs = E\Args::Page('args');
else
    $apiArgs = [];

if (!class_exists($apiClass))
    throw new \Exception("Api `{$apiClass}` does not exist.");

$site->api(new $apiClass($site, $apiArgs));


Espada::Initialize($site);
