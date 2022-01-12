<?php namespace EC\SPK;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MSPK extends E\Module
{

    private $header = null;
    private $abTemplate = null;

    private $moduleUris = [];
    private $modules = [];

    private $fields = [];
    private $texts = [];

    private $pages = [];

    private $appScripts = [];

    public function __construct(EC\Basic\MHead $header,
            EC\MABTemplate $ab_template)
    {
        parent::__construct();

        $this->header = $header;
        $this->abTemplate = $ab_template;

        $this->addTexts('SPK');
    }

    public function addAppScript($script)
    {
        $this->appScripts[] = $script;
    }

    public function addFields($fields_name, $fields)
    {
        if (is_array($fields))
            $fields = json_encode($fields);

        $this->fields[$fields_name] = $fields;
    }

    public function addModule($module_id, $elem_id = null,
            $module_name = null,
            $path = null)
    {
        if ($elem_id === null)
            $elem_id = $module_id;

        if ($module_name === null)
            $module_name = $module_id;

        $this->modules[] = [
            'id' => $module_id,
            'elemId' => $elem_id,
            'name' => $module_name
        ];

        if ($path !== null)
            $this->addScript($path);
    }

    public function addModuleLayout($layout_name)
    {
        $this->moduleLayouts[] = $layout_name;
    }

    public function addPage($name, $alias, $title)
    {
        $this->pages[] = [
            'name' => $name,
            'alias' => $alias,
            'title' => $title
        ];
    }

    public function addScript($path)
    {
        if ($path !== null) {
            $file_uri = E\Package::Uri_FromPath(
                    $path, 'front/spk', '.js');

            if ($file_uri === null)
                throw new \Exception("Script `{$path}` does not exist.");

            $this->moduleUris[] = $file_uri;
        }
    }

    public function addTexts($lang_path)
    {
        if (isset($this->texts[$lang_path]))
            return;

        $this->texts[$lang_path] = json_encode(EC\HText::GetTranslations(
                $lang_path)->getArray());
    }

    protected function _postInitialize(E\Site $site)
    {
        /* Module Uris */
        foreach ($this->moduleUris as $uri)
            $this->header->addScript($uri);

        /* Script */
        $html = '<script>';

        /* App Scripts */
        foreach ($this->appScripts as $app_script)
            $html .= "SPK.App(function() {" . $app_script . "});";

        /* EFields */
        $html .= 'SPK.App(function() {';
        foreach ($this->fields as $fields_name => $fields_json)
            $html .= "SPK.\$eFields.set('{$fields_name}', {$fields_json});";

        /* EText */
        foreach ($this->texts as $file_path => $Text_json)
            $html .= "SPK.\$eText.add('{$file_path}', {$Text_json});";

        $html .= '});';

        /* SPK */
        $layouts_uri = $this->abTemplate->getBuildUri() .
                '/layouts.json';

        $html .= 'SPK.Config()';

        $uri_args = E\Args::Uri_All();
        if (array_key_exists('_extra', $uri_args))
            $uri_args['_extra'] = [];

        $base = E\Uri::Page(null, $uri_args);
        $html .= ".base('{$base}')";

        $html .= ".layoutsInfo('{$layouts_uri}', '" . SITE_BASE . "')";

        foreach ($this->pages as $page) {
            $html .= ".page('{$page['name']}', '{$page['alias']}',
                    '{$page['title']}')";
        }

        foreach ($this->modules as $module) {
            $html .= ".module('{$module['id']}', '{$module['elemId']}',
                    '{$module['name']}')";
        }

        $html .= ';';

        $debug = EDEBUG ? 'true' : 'false';
        $html .= "SPK.Init({$debug});";

        $html .= '</script>';

        $this->header->addHtml($html);
    }

}
