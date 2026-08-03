<?php

use EC\Api\SApi;

defined('_ESPADA') or die(NO_ACCESS);

$siteClass = E\Args::Page('site');

$site = new $siteClass();

$apiClass = E\Args::Page('api');
if (E\Args::Page_Exists('args'))
    $apiArgs = E\Args::Page('args');
else
    $apiArgs = [];

if (!class_exists($apiClass))
    throw new \Exception("Api `{$apiClass}` does not exist.");

$site->api(new $apiClass($site, $apiArgs));


Espada::Initialize($site);
