<?php
defined('_ESPADA') or die(NO_ACCESS);


$site = new EC\SApi();

$apiClass = E\Args::Page('api');
if (E\Args::Page_Exists('args'))
    $apiArgs = E\Args::Page('args');
else
    $apiArgs = [];

if (!class_exists($apiClass))
    throw new \Exception("Api `{$apiClass}` does not exist.");

$site->api(new $apiClass($site, $apiArgs));


Espada::Initialize($site);
