<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MCache extends E\Module
{

    const Dir = PATH_CACHE . '/MCache';


    private $db = null;
    private $filesTable = null;

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct();

        $this->db = $db;

        if (!file_exists(self::Dir))
            mkdir(self::Dir, 0755, true);
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function newFile($user_id = null, $expires = 15 * 60)
    {
        $this->requirePreInitialize();

        $file_hash = EC\HHash::Generate(128);

        if (!$this->filesTable->update([[
            'Id' => null,
            'Hash' => $file_hash,
            'User_Id' => $user_id,
            'Expires' => time() + $expires
                ]]))
            return null;
        $file_id = $this->db->getInsertedId();

        return new CFile($this, $file_id, $user_id, $file_hash);
    }

    public function getFile($file_id, $user_id = null)
    {
        $this->requirePreInitialize();

        $where_conditions = [
            [ 'Id', '=', $file_id ]
        ];
        if ($user_id !== null)
            $where_conditions[] = [ 'User_Id', '=', $user_id ];

        $file_row = $this->filesTable->row_Where($where_conditions);
        if ($file_row === null)
            return null;

        return new CFile($this, $file_id, $file_row['User_Id'], $file_row['Hash']);
    }

    public function getFilePath($file_id, $file_hash)
    {
        return self::Dir . "/{$file_id}-{$file_hash}.cache";
    }

    public function releaseFile($file_id, $user_id, $file_hash)
    {
        $this->requirePreInitialize();

        if (file_exists($this->getFilePath($file_id, $file_hash)))
            unlink($this->getFilePath($file_id, $file_hash));

        $where_conditions = [
            [ 'Id', '=', $file_id ]
        ];
        if ($user_id !== null)
            $where_conditions[] = [ 'User_Id', '=', $user_id ];

        $this->filesTable->delete_Where($where_conditions);
    }

    protected function _preInitialize(E\Site $site)
    {
        parent::_preInitialize($site);

        $this->filesTable = new TFiles($this->db);
    }

    protected function _deinitialize()
    {

    }

}
