<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HLemonBee
{

    static public function CheckPanelPermissions(EC\Users\MUser $user, $panel)
    {
        foreach ($panel['permissions'] as $p_perm) {
            if (!$user->hasPermission($p_perm))
                return false;
        }

        return true;
    }

    static public function GetPanelUris(EC\Users\MUser $user, $panel_info)
    {
        $panel = self::ParsePanelInfo($panel_info);

        $uris = [];
        foreach ($panel['subpanels'] as $subpanel_name => $subpanel)
            $uris[$subpanel_name] = $panel['uri'] . $subpanel['alias'] . '/';

        return $uris;
    }

    static public function ParsePanelsPermissions(EC\Users\MUser $user, $panels)
    {
        foreach ($panels as $p_i => $p) {
            if (!self::CheckPanelPermissions($user, $p))
                unset($panels[$p_i]);

            foreach ($p['subpanels'] as $subpanel_name => $subpanel_info) {
                if (!self::CheckPanelPermissions($user, $subpanel_info))
                    unset($p['subpanels'][$subpanel_name]);
            }

            // $p['subpanels'] = array_values($p['subpanels']);
        }

        return array_values($panels);
    }

    static public function ParsePanelInfo($panel_info)
    {
        $required_properties = [ 'name', 'title', 'uri' ];
        $panel = [
            'name' => null,
            'title' => null,
            'uri' => null,
            'image' => null,

            'active' => false,

            'color' => '',
            'permissions' => [],

            'menu' => [],

            'subpanels' => []
        ];

        foreach ($panel_info as $p_name => $p) {
            if (!array_key_exists($p_name, $panel))
                throw new \Exception("Panel property `{$p_name}`" .
                        ' does not exist.');

            if ($p_name === 'subpanels') {
                /* Stable sort. */
                $t_p = [];
                foreach ($p as $key => $value) {
                    $t_p[$key] = $value;
                }

                EC\HSort::UA_Stable($p, function($a, $b) {
                    $a_order = array_key_exists('order', $a) ? $a['order'] : 0;
                    $b_order = array_key_exists('order', $b) ? $b['order'] : 0;

                    return $a_order - $b_order;
                });

                foreach ($p as $subpanel_name => $subpanel_info) {
                    $panel['subpanels'][$subpanel_name] =
                            self::ParseSubpanelInfo($subpanel_name, $subpanel_info);
                }
            } else if ($p_name === 'menu') {
                foreach ($p as $subpanel_name => $subpanel_info) {
                    $panel['menu'][] = self::ParseSubpanelInfo($subpanel_name, $subpanel_info);
                }
            } else
                $panel[$p_name] = $p;
        }

        foreach ($panel as $p_name => $p) {
            if (in_array($p_name, $required_properties) && $p === null)
                throw new \Exception("Panel property `{$p_name}` not set.");
        }

        if (E\Pages::GetName() === $panel['name'])
            $panel['active'] = true;

        // usort($panel['subpanels'], function($a, $b) {
        //     return $b['order'] - $a['order'];
        // });

        return $panel;
    }

    static public function ParseSubpanelInfo($subpanel_name, $subpanel_info)
    {
        $subpanel = [
            'name' => $subpanel_name,
            'order' => 0,
            'alias' => null,
            'args' => [],
            'title' => null,

            'color' => '',
            'faIcon' => '',
            'image' => '',
            'permissions' => []
        ];

        foreach ($subpanel_info as $p_name => $p) {
            if (!array_key_exists($p_name, $subpanel))
                throw new \Exception("Subpanel property `{$p_name}`" .
                        ' does not exist.');

            $subpanel[$p_name] = $p;
        }

        foreach ($subpanel as $p_name => $p) {
            if ($p === null)
                throw new \Exception("Subpanel property `{$p_name}` not set.");
        }

        return $subpanel;
    }


    static private function StableSort($arr, $fn)
    {

    }

}
