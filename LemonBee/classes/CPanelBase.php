<?php namespace EC\LemonBee;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

abstract class CPanelBase
{

    protected $requiredPermissions = [];

    public function __construct($required_permissions)
    {
        if ($required_permissions !== '')
            $this->addRequiredPermissions($required_permissions);
    }

    public function addRequiredPermissions($permissions)
    {
        $permissions = EC\HUsers::ParsePermissions($permissions);

        foreach ($permissions as $permission)
            if (!in_array($permission, $this->requiredPermissions))
                $this->requiredPermissions[] = $permission;
    }

    public function hasRequiredPermissions(EC\Users\MUser $user)
    {
        foreach ($this->requiredPermissions as $permission) {
            if (!$user->hasPermission($permission))
                return false;
        }

        return true;
    }

}
