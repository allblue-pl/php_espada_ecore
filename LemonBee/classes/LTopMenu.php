<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LTopMenu extends E\Layout
{

    public function __construct(EC\Users\MUser $user, $home_uri, $menu_items)
    {
        parent::__construct('LemonBee:topMenu', [
            'homeLink' => $home_uri,
            'menuItems' => $menu_items
        ]);
    }

}
