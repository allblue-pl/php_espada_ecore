<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Config\HConfig;
use EC\Database\MDatabase;
use EC\Session\MSession;

class MUser extends E\Module {
	const HASH_ROUNDS = 7;

	private MSession|null $session;
    private MDatabase $db;
    private string $type;

    private string $session_Name;

	// private $usersPermissions = [];

	private int $id = -1;
	private ?string $login = null;
	private array $groups = [];
	private array $permissions = [];

	/* Config */
	private ?array $testUsers = null;

	// private $salt = '';

    public function __construct(E\Site $site, MSession|null $session, MDatabase $database,
            $type = 'Default') {
		parent::__construct($site);

		$this->session = $session;
        $this->db = $database;
        $this->type = $type;

        $this->session_Name = "User_User_{$type}";
    }
    
    public function getGroups(): array {
        return $this->groups;
    }

    public function getType() {
        return $this->type;
    }

	/* Permissions */
	public function getPermissions() {
		return $this->permissions;
	}

	public function getPermissions_Default() {
		$groups = HConfig::GetRequired('Users', 'groups');
		if (array_key_exists('_default', $groups))
			return $groups['_default'];

		return [];
	}

	public function hasPermission(string $permission) {
		return in_array($permission, $this->permissions);
    }
    
    public function hasPermissions(array $permissions) {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission))
                return false;
        }

		return true;
	}

	public function isInGroup(string $groupName) {
		return in_array($groupName, $this->groups);
	}

	// /* Pages */
	// public function setPage_LogIn($page_path) {
	// 	$this->uris_LogIn = EUri::GetPage($page_path);
	// 	if ($this->uris_LogIn === null)
	// 		throw new \Exception("Page `$page_path` does not exist.");
	// }

	// public function getUri_LogIn() {
	// 	return $this->uris_LogIn;
	// }

	// public function setPage_LogOut($page_path) {
	// 	$this->uris_LogOut = EUri::GetPage($page_path);
	// 	if ($this->uris_LogOut === null)
	// 		throw new \Exception("Page `$page_path` does not exist.");
	// }

	// public function getUri_LogOut() {
	// 	return $this->uris_LogOut;
	// }

	/* User */
	public function isLoggedIn() {
		if ($this->id === -1)
            return false;

		return true;
	}

	public function getId() {
		return $this->id;
	}

	public function getLogin() {
		return $this->login;
    }
    
    public function initUser() {
		$user = $this->session->get($this->session_Name);

        if ($user === null) {
            $this->destroy();
            return;
        }

        $this->initUser_SetId($user['id'], $user['login']);
    }
    
    public function initUser_SetId(int $userId, string $userLogin) {
        $userInfo = HUsers::Get($this->db, $userId);

        if ($userInfo === null || !$userInfo['Active']) {
            $this->destroy();
            return;
        }

        $this->id = $userId;
        $this->login = $userLogin;
        $this->groups = $userInfo['Groups'];
        $this->permissions = array_merge($this->getPermissions_Default(),
                $userInfo['Groups_Permissions']);
    }

	/* Session */
	public function startSession(int $userId, string $userLogin) {
		$this->session->delete($this->session_Name);

		$user = [];
		$user['id'] = $userId;
        $user['login'] = $userLogin;

        $this->session->set($this->session_Name, $user);

        $this->initUser();
	}

	public function destroy() {
		$this->session->delete($this->session_Name);

		$this->id = -1;
		$this->login = '';
		$this->groups = [];
		$this->permissions = $this->getPermissions_Default();
	}

	/* Config */
	public function getTestUsers() {
		return $this->testUsers;
	}

	/* Initialization */
	protected function _preInitialize(E\Site $site): void {
		$this->_preInitialize_Config();
		$this->initUser();
		// $this->_preInitialize_Permissions();
	}

	private function _preInitialize_Config() {
		$this->testUsers = HUsers::GetTestUsers();
		// $this->salt = HConfig::GetRequired('Hash', 'salt');
	}

	// private function _preInitialize_Permissions()
	// {
	// 	if (!$this->isLoggedIn())
	// 		$this->permissions = [];
	// 	else {
	// 		$this->permissions =
	// 			HUsers::GetPermissions_FromGroups($this->groups);
	// 	}
	// }

}
