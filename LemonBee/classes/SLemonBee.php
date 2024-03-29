<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class SLemonBee extends EC\SBasic
{

    private $modulePath = null;

    private $defaultSetup = null;
    private $setup = [];

    public function __construct(string $abWebBuildPath, string $modulePath,
            string $userType = 'LemonBee')
    {
        parent::__construct();

        $this->modulePath = $modulePath;

        /* Modules */
        $this->addM('db', new EC\MDatabase());
        $this->addM('session', new EC\MSession($this->m->db));
        $this->addM('user', new EC\Users\MUser($this->m->session, $this->m->db, 
                $userType));

        $this->addM('abWeb', new EC\MABWeb($this->m->head, $abWebBuildPath));
        $this->addM('eLibs', new EC\MELibs($this->m->head));

        // $this->addM('spk', new EC\MSPK($this->m->head,
        //         $this->m->abTemplate));

        /* Root Layout */
        $this->setRootL(E\Layout::_('LemonBee:index', [
            'ModulePath' => $modulePath,
            'images' => [
                'favicon' => E\Uri::File('LemonBee:images/favicon.ico'),
                'appleTouchIcon' => E\Uri::File('LemonBee:images/appleTouchIcon.png'),
            ]
        ]));

        // print_r(EC\HText::GetTranslations('LemonBee:spk')->getArray());

        $this->m->eLibs->addTranslations('LemonBee');
        $this->m->eLibs->addTranslations_As('SPKTables', 'LemonBee:spkTables');

        $packageBase = '/dev/node_modules/spk-lemon-bee/';

        /* Default Setup */
        $this->defaultSetup = [
            'aliases' => [
                'account' => 'account',
                'main' => '',
                'logIn' => 'log-in',
            ],
            'images' => [
                'logo' => $packageBase . 'images/logo.png',
                'logo_Main' => $packageBase . 'images/logo.png',
            ],
            'panels' => [],
            'uris' => [
                'base' => E\Uri::Base(),
            ],
            'user' => [
                'loggedIn' => $this->m->user->isLoggedIn(),
                'login' => $this->m->user->getLogin(),
                'permissions' => $this->m->user->getPermissions(),
            ],

            'spkMessages' => [
                'images' => [
                    'loading' => $packageBase . 'images/messages/loading.gif',
                    'success' => $packageBase . 'images/messages/success.png',
                    'failure' => $packageBase . 'images/messages/failure.png',
                ],
            ],
        ];

        
    }

    public function lbSetup(array $setup)
    {
        $this->setup = array_replace_recursive($this->setup, $setup);
    }


    /* E\Site */
    protected function _postInitialize()
    {
        parent::_postInitialize();

        $setup = array_replace_recursive($this->defaultSetup, $this->setup);

        $this->m->eLibs->setField('lbSetup', $setup);
    }
    /* / E\Site */

}
