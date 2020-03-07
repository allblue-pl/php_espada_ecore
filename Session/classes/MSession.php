<?php namespace EC\Session;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MSession extends E\Module
{

    private $db = null;

	public function __construct(EC\MDatabase $db, $expirationTime = 0, $base = '/')
	{
        $this->db = $db;

        ini_set('session.cookie_lifetime', $expirationTime);
        ini_set('session.gc_maxlifetime', $expirationTime);

        session_set_save_handler(  
            [ $this, "_sessionHandlers_Open" ],  
            [ $this, "_sessionHandlers_Close" ],  
            [ $this, "_sessionHandlers_Read" ],  
            [ $this, "_sessionHandlers_Write" ],  
            [ $this, "_sessionHandlers_Destroy" ],  
            [ $this, "_sessionHandlers_GC" ],  
        );
	}

	protected function _preInitialize(E\Site $site)
	{
		session_start();
	}

	protected function _postInitialize(E\Site $site)
	{
		session_write_close();
	}

	protected function _deinitialize()
	{
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

	public function &get($name)
	{
		$this->requirePreInitialize();

		if (isset($_SESSION[$name]))
			return $_SESSION[$name];

		$null = null;
		return $null;
	}

	public function set($name, $value)
	{
		$this->requirePreInitialize();
		$this->requireBeforePostInitialize();

		// echo "before";
		// print_r($_SESSION);

		$_SESSION[$name] = $value;

		// echo "after";
		// print_r($_SESSION);
	}

	public function delete($name)
	{
		$this->requirePreInitialize();
		$this->requireBeforePostInitialize();

		if (isset($_SESSION[$name]))
			unset($_SESSION[$name]);
	}

	public function destroy()
	{
		$this->requirePreInitialize();
		$this->requireBeforePostInitialize();

		$_SESSION = [];

		session_destroy();
	}

	public function &__get($name)
	{
		return $this->get($name);
	}

	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function getToken()
	{
		return session_id();
    }
    

    /* Session Handlers */
    public function _sessionHandlers_Close()
    {
        return true;
    }

    public function _sessionHandlers_Destroy($id)
    {
        if (!(new TSessions($this->db))->delete_Where([
            [ 'Id', '=', $id ],
                ]))
            return false;
        
        return true;
    }

    public function _sessionHandlers_GC($max)
    {
        if (!(new TSessions($this->db))->delete_Where([
            [ 'Access', '<', $max ],
                ]))
            return false;
        
        return true;
    }

    public function _sessionHandlers_Open()
    {
        return true;
    }

    public function _sessionHandlers_Read($id)
    {
        $row = (new TSessions($this->db))->row_Where([
            [ 'Id', '=', $id ],
        ]);
        
        if ($row === null)
            return '';

        return $row['Data'];
    }

    public function _sessionHandlers_Write($id, $data)
    {
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
