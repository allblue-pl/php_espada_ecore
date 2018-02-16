<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

abstract class MPanel extends E\Module
{

    private $site = null;

    private $defaultViewName = null;
    private $views = [];

    public function __construct(SLemonBee $site)
    {
        parent::__construct();

        $this->site = $site;
    }

    public function lbAddView($panel_info, $subpanel_name, $module_name,
            $module_fields = [])
    {
        $panel = HLemonBee::ParsePanelInfo($panel_info);

        if (!HLemonBee::CheckPanelPermissions($this->site->m->user, $panel))
            return;

        if (!isset($panel['subpanels'][$subpanel_name]))
            throw new \Exception("Subpanel `{$subpanel_name}` does not exist.");

        $subpanel = $panel['subpanels'][$subpanel_name];

        if (!HLemonBee::CheckPanelPermissions($this->site->m->user, $subpanel))
            return;

        $this->views[$subpanel['name']] = [
            'name' => $subpanel['name'],
            'title' => $subpanel['title'],
            'alias' => $subpanel['alias'],
            'faIcon' => $subpanel['faIcon'],
            'image' => $subpanel['image'],
            'moduleName' => $module_name
        ];

        if ($this->defaultViewName === null)
            $this->defaultViewName = $subpanel['name'];

        $this->site->m->spk->addPage($subpanel['name'], $subpanel['alias'],
                $subpanel['title']);
        $this->site->m->spk->addFields($module_name, $module_fields);
    }

    protected function _postInitialize(E\Site $site)
    {
        parent::_postInitialize($site);

        $site->m->spk->addScript('LemonBee:Panel');
        $site->m->spk->addModule('eLemonBee_Panel');

        $this->_lbInitialize($site);

        $site->m->spk->addFields('eLemonBee_Panel', [
            'defaultViewName' => $this->defaultViewName,
            'views' => $this->views
        ]);

        $site->addL('content', new EC\LSPK('eLemonBee_Panel'));
    }

    abstract protected function _lbInitialize(SLemonBee $site);

}
