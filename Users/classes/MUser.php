<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MUser extends E\Module
{

	const HASH_ROUNDS = 7;

	private $session = null;
	private $database = null;

	private $usersPermissions = [];

	private $id = -1;
	private $login = null;
	private $groups = [];
	private $permissions = [];

	/* Config */
	private $testUsers = null;

	private $salt = '';

    public function __construct(EC\MSession $session, EC\MDatabase $database,
            $type = 'Default')
	{
		parent::__construct();

		$this->session = $session;
        $this->db = $database;
        $this->type = $type;

        $this->session_Name = "User_User_{$type}";
    }
    
    public function getType()
    {
        return $this->type;
    }

	/* Permissions */
	public function getPermissions()
	{
		return $this->permissions;
	}

	public function getPermissions_Default()
	{
		$groups = EC\HConfig::GetRequired('Users', 'groups');
		if (array_key_exists('_default', $groups))
			return $groups['_default'];

		return [];
	}

	public function hasPermission($permission)
	{
		return in_array($permission, $this->permissions);
    }
    
    public function hasPermissions(array $permissions)
	{
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission))
                return false;
        }

		return true;
	}

	public function isInGroup($group_name)
	{
		return in_array($group_name, $this->groups);
	}

	/* Pages */
	public function setPage_LogIn($page_path)
	{
		$this->uris_LogIn = EUri::GetPage($page_path);
		if ($this->uris_LogIn === null)
			throw new \Exception("Page `$page_path` does not exist.");
	}

	public function getUri_LogIn()
	{
		return $this->uris_LogIn;
	}

	public function setPage_LogOut($page_path)
	{
		$this->uris_LogOut = EUri::GetPage($page_path);
		if ($this->uris_LogOut === null)
			throw new \Exception("Page `$page_path` does not exist.");
	}

	public function getUri_LogOut()
	{
		return $this->uris_LogOut;
	}

	/* User */
	public function isLoggedIn()
	{
		if ($this->id === -1)
			return false;

		return true;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getLogin()
	{
		return $this->login;
	}

	/* Session */
	public function startSession($user_id, $user_login)
	{
		$this->session->delete($this->session_Name);

		$user = [];
		$user['id'] = $user_id;
        $user['login'] = $user_login;

		$this->session->set($this->session_Name, $user);
	}

	public function destroy()
	{
		$this->session->delete($this->session_Name);

		$this->id = -1;
		$this->login = '';
		$this->groups = [];
		$this->permissions = $this->getPermissions_Default();
	}

	/* Config */
	public function getTestUsers()
	{
		return $this->testUsers;
	}

	/* Initialization */
	protected function _preInitialize(E\Site $site)
	{
		$this->_preInitialize_Config();
		$this->_preInitialize_User();
		// $this->_preInitialize_Permissions();
	}

	private function _preInitialize_Config()
	{
		$this->testUsers = HUsers::GetTestUsers();
		$this->salt = EC\HConfig::GetRequired('Hash', 'salt');
	}

	private function _preInitialize_User()
	{
		$user = $this->session->get($this->session_Name);

		if ($user !== null) {
			$user_info = HUsers::Get($this->db, $user['id']);

			if ($user_info !== null && $user_info['Active']) {
				$this->id = $user['id'];
				$this->login = $user['login'];
				$this->groups = $user_info['Groups'];
				$this->permissions = array_merge($this->getPermissions_Default(),
						$user_info['Groups_Permissions']);

				return;
			}
		}

		$this->destroy();
	}

	// private function _preInitialize_Permissions()
	// {
	// 	if (!$this->isLoggedIn())
	// 		$this->permissions = [];
	// 	else {
	// 		$this->permissions =
	// 			EC\HUsers::GetPermissions_FromGroups($this->groups);
	// 	}
	// }

}
