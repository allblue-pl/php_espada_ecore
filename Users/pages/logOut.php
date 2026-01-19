<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Basic\SBasic;
use EC\Database\MDatabase;
use EC\Session\MSession;

$site = new SBasic();

$site->addM('db', new MDatabase());
$site->addM('session', new MSession($site->m->db));
$site->addM('user', new MUser($site->m->session, $site->m->db, 
        E\Args::Page('userType')));

$site->onPreInitialize(function() use ($site) {
    $site->m->user->destroy();

    $redirectUri = '/';
    if (E\Args::Get_Exists('redirectUri'))
        $redirectUri = base64_decode(E\Args::Get('redirectUri'));

    \Espada::Redirect($redirectUri);
});


\Espada::Initialize($site);