<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LChangePassword extends EC\LSPK {

    public function __construct(EC\MSPK $abf, $user_api_uri) {
        parent::__construct('eUsers_ChangePassword');

        $abf->addScript('Users:ChangePassword');
        $abf->addModule('eUsers_ChangePassword');
        $abf->addTexts('Users');
        $abf->addFields('eUsers_ChangePassword', [
            'apiUris' => [
                'changePassword' => $user_api_uri . 'change-password'
            ]
        ]);
    }

}
