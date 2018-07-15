<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class SLemonBee extends EC\SBasic
{
    private $defaultSetup = null;
    private $setup = [];

    public function __construct(string $abWebBuildPath, string $userType = 'LemonBee')
    {
        parent::__construct();

        /* Modules */
        $this->addM('session', new EC\MSession());
        $this->addM('db', new EC\MDatabase());
        $this->addM('user', new EC\Users\MUser($this->m->session, $this->m->db, 
                $userType));

        $this->addM('abWeb', new EC\MABWeb($this->m->header, $abWebBuildPath));
        $this->addM('eLibs', new EC\MELibs($this->m->header));

        // $this->addM('spk', new EC\MSPK($this->m->header,
        //         $this->m->abTemplate));

        /* Root Layout */
        $this->setRootL(E\Layout::_('LemonBee:index', [
            'images' => [
                'favicon' => E\Uri::File('LemonBee:images/favicon.ico'),
                'appleTouchIcon' => E\Uri::File('LemonBee:images/appleTouchIcon.png'),
            ]
        ]));

        // print_r(EC\HText::GetTranslations('LemonBee:spk')->getArray());

        /* Default Setup */
        $this->defaultSetup = [
            'aliases' => [
                'account' => 'account',
                'main' => '',
                'logIn' => 'log-in',
            ],
            'images' => [
                'messages' => [],
            ],
            'panels' => [],
            'texts' => EC\HText::GetTranslations('LemonBee:spk')->getArray(),
            'uris' => [
                'base' => E\Uri::Base(),
                'api' => E\Uri::Base() . '/api',
            ],
            'user' => [
                'loggedIn' => false,
                'login' => 'guest',
                'permissions' => [],
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

        $fields = array_replace_recursive($this->defaultSetup, $this->setup);
        $fieldsString = str_replace("'", "\\'", json_encode($fields));

        /* Replace submodules modules with require. */
        $submodulesString = '';
        for ($i = 0; $i < count($fields['panels']); $i++) {
            $panel = $fields['panels'][$i];
            for ($j = 0; $j < count($panel['subpanels']); $j++) {
                $subpanel = $panel['subpanels'][$j];
                $submodulesString .= "
    lbSetup.panels[{$i}].subpanels[{$j}].module = require('{$subpanel['module']['package']}')
            .{$subpanel['module']['module']}.{$subpanel['module']['class']};";
            }
        }

        $script = <<<HTML
<script type="text/javascript">
    let eLibs = jsLibs.require('e-libs');
    let lbSetup = JSON.parse('${fieldsString}');

${submodulesString}

    eLibs.eFields.set('lbSetup', lbSetup);
</script>
HTML;

        $this->m->header->addHtml($script);

        // $this->m->spkEFields->add('lb', $fields);

        // $this->m->spk->addAppScript(
        //     "SPK.\$abNotifications.SetImages({$images_json});" .
        //     "SPK.\$abDate.utcOffset = 0;" .

        //     "SPK.\$eLang.tag = 'pl-PL';"
        // ); 
    }
    /* / E\Site */

}
