<?php namespace EC\Cache;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Hash\HHash;

class MCache extends E\Module {
    const Dir = PATH_CACHE . '/MCache';


    private MDatabase $db;
    private ?TFiles $filesTable = null;


    public function __construct(E\Site $site, MDatabase $db) {
        parent::__construct($site);

        $this->db = $db;

        if (!file_exists(self::Dir))
            mkdir(self::Dir, 0755, true);
    }

    public function getDir() {
        return self::Dir;
    }

    public function newFile(?int $userId = null, int $expires = 60 * 60) {
        $this->requirePreInitialize();

        $fileHash = HHash::Generate(128);

        if (!$this->filesTable->update([[
            'Id' => null,
            'Hash' => $fileHash,
            'User_Id' => $userId,
            'Expires' => time() + $expires
                ]]))
            return null;
        $fileId = $this->filesTable->getLastInsertedId();

        return new CFile($this, $fileId, $userId, $fileHash);
    }

    public function getFile(int $fileId, ?int $userId = null) {
        $this->requirePreInitialize();

        $where_conditions = [
            [ 'Id', '=', $fileId ]
        ];
        if ($userId !== null)
            $where_conditions[] = [ 'User_Id', '=', $userId ];

        $file_row = $this->filesTable->row_Where($where_conditions);
        if ($file_row === null)
            return null;

        return new CFile($this, $fileId, $file_row['User_Id'], $file_row['Hash']);
    }

    public function getFilePath(int $fileId, string $fileHash) {
        return self::Dir . "/{$fileId}-{$fileHash}.cache";
    }

    public function releaseFile(int $fileId, ?int $userId, string $fileHash) {
        $this->requirePreInitialize();

        if (file_exists($this->getFilePath($fileId, $fileHash)))
            unlink($this->getFilePath($fileId, $fileHash));

        $where_conditions = [
            [ 'Id', '=', $fileId ]
        ];
        if ($userId !== null)
            $where_conditions[] = [ 'User_Id', '=', $userId ];

        $this->filesTable->delete_Where($where_conditions);
    }

    protected function _preInitialize(E\Site $site) {
        $this->filesTable = new TFiles($this->db);

        $rFiles_Expired = $this->filesTable->select_Where([
            [ 'Expires', '<', time() ],
        ]);

        foreach ($rFiles_Expired as $rFile) {
            $filePath = $this->getFilePath($rFile['Id'], $rFile['Hash']);
            if (file_exists($filePath))
                unlink($filePath);
        }

        $this->filesTable->delete_Where([
            [ 'Expires', '<', time() ],
        ]);
    }

    protected function _deinitialize() {

    }

}
