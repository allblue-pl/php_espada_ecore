<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;

class MSession extends E\Module {
    private MDatabase $db;
    private CSessionHandler $sessionHandler;

	public function __construct(E\Site $site, MDatabase $db, $expirationTime = 0) {
        parent::__construct($site);

        $this->db = $db;
        $this->sessionHandler = new CSessionHandler($this);

        ini_set('session.cookie_lifetime', $expirationTime);
        ini_set('session.gc_maxlifetime', $expirationTime);

        session_set_save_handler($this->sessionHandler);
	}

	protected function _preInitialize(E\Site $site) {
		session_start();
	}

	protected function _postInitialize(E\Site $site) {
		session_write_close();
	}

	protected function _deinitialize() {
		// $cookie_params = session_get_cookie_params();
		//
		// $cookie_expire = $cookie_params['lifetime'] === 0 ?
		// 					0 : $cookie_params['lifetime'];
		//
		// $_COOKIE[session_name()] = $this->oldSessionId;
		// setcookie(session_name(), $this->oldSessionId,
		// 		time() + $cookie_params['lifetime'], $cookie_params['domain'],
		// 		$cookie_params['secure'], $cookie_params['httponly']);
	}

	public function &get(string $name) {
		$this->requirePreInitialize();

		if (isset($_SESSION[$name]))
			return $_SESSION[$name];

		$null = null;
		return $null;
	}

	public function set(string $name, mixed $value) {
		$this->requirePreInitialize();
		$this->requireBeforePostInitialize();

		// echo "before";
		// print_r($_SESSION);

		$_SESSION[$name] = $value;

		// echo "after";
		// print_r($_SESSION);
	}

	public function delete(string $name) {
		$this->requirePreInitialize();
		$this->requireBeforePostInitialize();

		if (isset($_SESSION[$name]))
			unset($_SESSION[$name]);
	}

	public function destroy() {
		$this->requirePreInitialize();
		$this->requireBeforePostInitialize();

		$_SESSION = [];

		session_destroy();
	}

	public function &__get(string $name) {
		return $this->get($name);
    }
    
    public function __isset(string $name) {
        return isset($_SESSION[$name]);
    }

	public function __set(string $name, mixed $value) {
		$this->set($name, $value);
	}

	public function getToken() {
		return session_id();
    }
    

    /* Session Handlers */
    public function sessionHandlers_Close() {
        return true;
    }

    public function sessionHandlers_Destroy(string $id) {
        if (!$this->db->isConnected())
            return true;

        if (!(new TSessions($this->db))->delete_Where([
            [ 'Id', '=', $id ],
                ]))
            return false;
        
        return true;
    }

    public function sessionHandlers_GC(int $max) {
        if (!$this->db->isConnected())
            return true;

        if (!(new TSessions($this->db))->delete_Where([
            [ 'Access', '<', $max ],
                ]))
            return false;
        
        return true;
    }

    public function sessionHandlers_Open() {
        return true;
    }

    public function sessionHandlers_Read(string $id) {
        if (!$this->db->isConnected())
            return true;

        $row = (new TSessions($this->db))->row_Where([
            [ 'Id', '=', $id ],
        ]);
        
        if ($row === null)
            return '';

        return $row['Data'];
    }

    public function sessionHandlers_Write(string $id, string $data) {
        if (!$this->db->isConnected())
            return true;

        if (!(new TSessions($this->db))->update([[
            'Id' => $id,
            'Data' => $data,
            'Access' => time(),
                ]]))
            return false;
        
        return true;
    }
    /* / Session Handlers */


}
