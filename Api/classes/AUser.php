<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E;
use EC\Basic\SUser;
use EC\Database\MDatabase;
use EC\Session\MSession;
use EC\Users\MUser;

class AUser extends AApi {
    private $actionRequiredPermissions = [];
    private $requiredPermissions = null;
    private MUser $user;
    private MSession $session;
    private MDatabase $db;

    public function __construct(SUserApi $site, array $requiredPermissions = []) {
        parent::__construct($site);

        $this->requiredPermissions = $requiredPermissions;

        $this->db = $site->getDB();
        $this->session = $site->getSession();
        $this->user = $site->getUser();
    }

    public function actionR(string $actionName, string $fn, $argInfos = [],
            $requiredPermissions = []) {
        $this->actionRequiredPermissions[$actionName] = $requiredPermissions;
        $this->action($actionName, $fn, $argInfos);
    }

    public function actionR_Bytes($actionName, $fn, $argInfos = [],
            $required_permissions = []) {
        $this->actionRequiredPermissions[$actionName] = $required_permissions;
        $this->action_Bytes($actionName, $fn, $argInfos);
    }

    public function getDB(): MDatabase {
        return $this->db;
    }

    public function getSession(): MSession {
        return $this->session;
    }

    public function getUser(): MUser {
        return $this->user;
    }

    public function getResult($actionName, $args) {
        $user = $this->user;

        if (array_key_exists($actionName, $this->actionRequiredPermissions)) {
            $requiredPermissions = array_merge($this->requiredPermissions,
                    $this->actionRequiredPermissions[$actionName]);
        } else
            $requiredPermissions = $this->requiredPermissions;

        foreach ($requiredPermissions as $p) {
            if (!$user->hasPermission($p)) {
                return CResult::Failure('Permission denied.')
                        ->debug('Required permission: ' . $p);
            }
        }

        return parent::getResult($actionName, $args);
    }
}
