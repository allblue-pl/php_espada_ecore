<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class ABasic extends AApi {

    private $actionRequiredPermissions = [];
    private $requiredPermissions = null;

    public function __construct(EC\SApi $site, $userType = 'Default', 
            $requiredPermissions = [])
    {
        parent::__construct($site);

        $this->requiredPermissions = $requiredPermissions;

        $site->addM('db', new EC\MDatabase());
        $site->addM('session', new EC\MSession($site->m->db));
        $site->addM('user', new EC\Users\MUser($site->m->session,
                $site->m->db, $userType));
    }

    public function actionR(string $action_name, string $fn, $arg_infos = [],
            $requiredPermissions = [])
    {
        $this->actionRequiredPermissions[$action_name] = $requiredPermissions;
        $this->action($action_name, $fn, $arg_infos);
    }

    public function actionR_Bytes($action_name, $fn, $arg_infos = [],
            $required_permissions = [])
    {
        $this->actionRequiredPermissions[$action_name] = $required_permissions;
        $this->action_Bytes($action_name, $fn, $arg_infos);
    }

    public function getResult($action_name, $args)
    {
        $user = $this->getSite()->m->user;

        if (array_key_exists($action_name, $this->actionRequiredPermissions)) {
            $requiredPermissions = array_merge($this->requiredPermissions,
                    $this->actionRequiredPermissions[$action_name]);
        } else
            $requiredPermissions = $this->requiredPermissions;

        foreach ($requiredPermissions as $p) {
            if (!$user->hasPermission($p)) {
                return EC\Api\CResult::Failure('Permission denied.')
                        ->debug('Required permission: ' . $p);
            }
        }

        return parent::getResult($action_name, $args);
    }

}
