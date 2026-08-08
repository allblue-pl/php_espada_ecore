<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\ABWeb\MABWeb;
use EC\Basic\SBasic;
use EC\Database\MDatabase;
use EC\ELibs\MELibs;
use EC\Session\MSession;
use EC\Users\MUser;

class SLemonBee extends SBasic {
    private array $defaultSetup;
    private array $setup = [];
    private MUser $user;
    private MSession $session;
    private MDatabase $db;
    private MABWeb $abWeb; /**  @phpstan-ignore property.onlyWritten */
    private MELibs $eLibs;

    public function __construct(string $abWebBuildPath, string $modulePath,
            string $userType = 'LemonBee') {
        parent::__construct();

        /* Modules */
        $this->db = new MDatabase($this);
        $this->session = new MSession($this, $this->db);
        $this->user = new MUser($this, $this->session, $this->db, $userType);
        $this->abWeb = new MABWeb($this, $this->getHead(), $abWebBuildPath);
        $this->eLibs = new MELibs($this, $this->getHead());

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

        $this->eLibs->addTranslations('LemonBee');
        $this->eLibs->addTranslations_As('SPKTables', 'LemonBee:spkTables');

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
                'loggedIn' => $this->user->isLoggedIn(),
                'login' => $this->user->getLogin(),
                'permissions' => $this->user->getPermissions(),
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

    public function lbSetup(array $setup) {
        $this->setup = array_replace_recursive($this->setup, $setup);
    }


    /* E\Site */
    protected function _postInitialize(): void {
        $setup = array_replace_recursive($this->defaultSetup, $this->setup);

        $this->eLibs->setField('lbSetup', $setup);
    }
    /* / E\Site */

}
