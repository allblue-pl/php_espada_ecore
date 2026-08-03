<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Basic\SBasic;
use EC\Database\MDatabase;
use EC\Session\MSession;

$site = new SBasic();

$db = new MDatabase($site);
$session = new MSession($site, $db);
$user = new MUser($site, $session, $db, E\Args::Page('userType'));

$site->onPreInitialize(function() use ($site, $user) {
    $user->destroy();

    $redirectUri = '/';
    if (E\Args::Get_Exists('redirectUri'))
        $redirectUri = base64_decode(E\Args::Get('redirectUri'));

    \Espada::Redirect($redirectUri);
});


\Espada::Initialize($site);