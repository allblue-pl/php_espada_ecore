<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class SLemonBee extends EC\SBasic
{

    private $requiredPermissions = [];

    private $panels = [];
    private $menuItems = [];

    private $lRoot = null;

    private $userName = null;

    private $uris = [
        'home' => null,
        'logIn' => null,
        'logOut' => null,
        'userInfo' => null
    ];

    private $apiUris = [
        'user' => null
    ];

    private $defaultPanelClass = '';

    public function __construct($ab_template_name, $panel_class)
    {
        parent::__construct();

        /* Root Layout */
        $this->lRoot = new LRoot();
        $this->lRoot->setPanelClass($panel_class);

        $this->setRootL($this->lRoot);

        /* Modules */
        $this->addM('session', new EC\MSession());
        $this->addM('db', new EC\MDatabase());
        $this->addM('user', new EC\Users\MUser($this->m->session,
                $this->m->db));

        $this->addM('abTemplate', new EC\MABTemplate($this->m->header,
                $ab_template_name));
        $this->addM('spk', new EC\MSPK($this->m->header,
                $this->m->abTemplate));

        /* SPK */
        $this->m->spk->addTexts('LemonBee');
    }

    public function lbAddMenuItem($title, $uri)
    {
        $this->menuItems[] = [
            'title' => $title,
            'uri' => $uri
        ];
    }

    public function lbAddPanel($panel_info)
    {
        // if ($this->isInitialized())
        //     throw new \Exception('Cannot add panel after initialization.');

        $panel = HLemonBee::ParsePanelInfo($panel_info);

        $this->panels[] = $panel;
        $this->lbAddMenuItem($panel['title'], $panel['uri']);
    }

    public function lbAddRequiredPermissions($permissions)
    {
        $this->requiredPermissions = array_merge($this->requiredPermissions,
                $permissions);
    }

    public function lbGetApiUri($name)
    {
        if (!isset($this->apiUris[$name]))
            throw new \Exception("Api uri {$name} does not exist.");

        if ($this->apiUris[$name] === null)
            throw new \Exception("Api uri {$name} not set.");

        return $this->apiUris[$name];
    }

    public function lbGetUri($name)
    {
        if (!array_key_exists($name, $this->uris))
            throw new \Exception("Uri {$name} does not exist.");

        if ($this->uris[$name] === null)
            throw new \Exception("Uri {$name} not set.");

        return $this->uris[$name];
    }

    public function lbGetUserName()
    {
        if (!$this->isInitialized())
            throw new \Exception('Not initialized.');

        return $this->userName === null ?
                $this->m->user->getLogin() : $this->userName;
    }

    public function lbGetPanels()
    {
        if (!$this->isInitialized())
            throw new \Exception('Cannot get panels before initialization.');

        return HLemonBee::ParsePanelsPermissions($this->m->user,
                $this->panels);
    }

    public function lbSetApiUris($api_uris)
    {
        foreach ($api_uris as $au_name => $au) {
            if (!array_key_exists($au_name, $this->apiUris))
                throw new \Exception("Api uri `{$au_name}` does not exist.");

            $this->apiUris[$au_name] = $au;
        }
    }

    public function lbSetUris($uris)
    {
        foreach ($uris as $u_name => $u) {
            if (!array_key_exists($u_name, $this->uris))
                throw new \Exception("Api uri `{$u_name}` does not exist.");

            $this->uris[$u_name] = $u;
        }
    }

    public function lbSetUserName($user_name)
    {
        $this->userName = $user_name;
    }

    public function lbSetRequiredPermissions($permissions)
    {
        if (!is_array($permissions))
            throw new \Exception('`permissions` must be an array.');

        $this->requiredPermissions = $permissions;
    }

    /* Pages */
    public function lbSetPage_LogIn()
    {
        $this->lbSetRequiredPermissions([]);

        $this->onPreInitialize(function(EC\SLemonBee $site) {
            $this->lRoot->setLayout_LogIn();

            $this->addL('content', new EC\Users\LLogInForm($site->m->spk,
                    $this->apiUris['user'], $this->uris['logIn']));

            if ($this->m->user->isLoggedIn())
                \Espada::Redirect($this->uris['home']);
        });
    }

    public function lbSetPage_LogOut()
    {
        $this->onPostInitialize(function(EC\SLemonBee $site) {
            $this->m->user->destroy();
            \Espada::Redirect($this->uris['logIn']);
        });
    }

    public function lbSetPage_MainPanel()
    {
        $this->setLayout_Panel();

        $this->onPostInitialize(function() {
            $this->addL('content', new LMainPanel($this));
        });
    }

    public function lbSetPage_Panel(MPanel $panel)
    {
        $this->setLayout_Panel();

        $this->addM('lbPanel', $panel);
    }

    public function lbSetPage_UserInfo()
    {
        $this->setLayout_Panel();

        $this->addL('content', new EC\Users\LChangePassword($this->m->spk,
                $this->apiUris['user']));
    }

    protected function _initialize()
    {
        parent::_initialize();

        $this->_initialize_CheckUris();

        if (count($this->requiredPermissions) > 0)
            if (!$this->m->user->isLoggedIn())
                \Espada::Redirect($this->uris['logIn']);
        foreach ($this->requiredPermissions as $required_permission) {
            if (!$this->m->user->hasPermission($required_permission))
                \Espada::Redirect($this->uris['logIn']);
        }

        /* SPK Notifications */
        $images_json = json_encode([
            'loading' => E\Uri::File('LemonBee:images/notifications/loading.gif'),
            'success' => E\Uri::File('LemonBee:images/notifications/message-success.png'),
            'failure' => E\Uri::File('LemonBee:images/notifications/message-failure.png')
        ]);

        // $utc_time = new \DateTime('now', new \DateTimeZone('UTC'));
        // $timezone = new \DateTimeZone('Europe/Warsaw');
        // $timezone_offset = $timezone->getOffset($utc_time) / 60 / 60;

        $this->m->spk->addAppScript(
            "SPK.\$abNotifications.SetImages({$images_json});" .
            "SPK.\$abDate.utcOffset = 0;" .

            "SPK.\$eLang.tag = 'pl-PL';"
        ); 
    }

    private function _initialize_CheckUris()
    {
        foreach ($this->uris as $uri_name => $uri) {
            if ($uri === null)
                throw new \Exception("Uri `{$uri_name}` not set.");
        }

        foreach ($this->apiUris as $au_name => $au) {
            if ($au === null)
                throw new \Exception("Uri `{$au_name}` not set.");
        }
    }

    private function setLayout_LogIn()
    {
        $this->onPostInitialize(function(EC\SLemonBee $site) {
            $this->lRoot->setLayout_Panel();
        });
    }

    private function setLayout_Panel()
    {
        $this->onPostInitialize(function(EC\SLemonBee $site) {
            $this->lRoot->setLayout_Panel();

            $this->addL('topMenu', new LTopMenu(
                    $this->m->user, $this->uris['home'],
                    $this->lbGetPanels()));

            $this->addL('userInfo', new LUserInfo($this));
        });
    }

}
