<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

$site = new EC\SBasic();

$site->addM('session', new EC\MSession());
$site->addM('db', new EC\MDatabase());
$site->addM('user', new EC\Users\MUser($site->m->session, $site->m->db));

$site->onPreInitialize(function() use ($site) {
    $site->m->user->destroy();

    $redirectUri = '/';
    if (E\Args::Get_Exists('redirectUri'))
        $redirectUri = base64_decode(E\Args::Get('redirectUri'));

    \Espada::Redirect($redirectUri);
});


\Espada::Initialize($site);