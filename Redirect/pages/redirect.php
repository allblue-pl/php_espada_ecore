<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

if (!E\Args::Page_Exists('uri'))
    throw new \Exception('Uri not set.');

\Espada::Redirect(SITE_DOMAIN . E\Args::Page('uri'));
