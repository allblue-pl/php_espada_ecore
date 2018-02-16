<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class ABasic extends AApi
{

    private $actionRequiredPermissions = [];
    private $requiredPermissions = null;

    public function __construct(EC\SApi $site, $required_permissions = [])
    {
        parent::__construct($site);

        $this->requiredPermissions = $required_permissions;

        $site->addM('session', new EC\MSession());
        $site->addM('db', new EC\MDatabase());
        $site->addM('user', new EC\Users\MUser($site->m->session,
                $site->m->db));
    }

    public function actionR($action_name, $fn, $arg_infos = [],
            $required_permissions = [])
    {
        $this->actionRequiredPermissions[$action_name] = $required_permissions;
        $this->action($action_name, $fn, $arg_infos);
    }

    public function getResult($action_name, $args)
    {
        $user = $this->getSite()->m->user;

        if (array_key_exists($action_name, $this->actionRequiredPermissions)) {
            $required_permissions = array_merge($this->requiredPermissions,
                    $this->actionRequiredPermissions[$action_name]);
        } else
            $required_permissions = $this->requiredPermissions;

        foreach ($required_permissions as $p) {
            if (!$user->hasPermission($p)) {
                return EC\Api\CResult::Failure('Permission denied.')
                        ->debug('Required permission: ' . $p);
            }
        }

        return parent::getResult($action_name, $args);
    }

}
