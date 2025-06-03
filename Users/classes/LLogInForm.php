<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LLogInForm extends EC\LSPK {

    public function __construct(EC\MSPK $abf,
            $api_page_uri, $redirect_page_uri) {
        parent::__construct('eUsers_LogIn');

        $abf->addScript('Users:LogIn');
        $abf->addModule('eUsers_LogIn');
        $abf->addTexts('Users');
        $abf->addFields('eUsers_LogIn', [
            'uris' => [
                "api" => $api_page_uri . 'log-in',
                "redirect" => $redirect_page_uri
            ]
        ]);
    }

}
