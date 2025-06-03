<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CTask {

    private $hash = null;
    private $userId = null;

    private $new = false;
    private $finished = false;
    private $destroyed = false;

    private $info = null;
    private $data = null;

    private $requiresUpdate = false;

    public function __construct($hash, $user_id, $finished, array $info,
            array $data) {
        $this->hash = $hash === null ? EC\HHash::Generate(128) : $hash;
        $this->userId = $user_id;

        $this->new = $hash === null ? true : false;
        $this->finished = $finished;
        $this->destroyed = false;

        $this->info = $info;
        $this->data = $data;
    }

    public function destroy() {
        if (!$this->destroyed)
            $this->requiresUpdate = true;

        $this->destroyed = true;
    }

    public function finish($destroy = false) {
        if (!$this->finished)
            $this->requiresUpdate = true;

        $this->finished = true;

        if ($destroy)
            $this->destroy();
    }

    public function &getData() {
        $this->requiresUpdate = true;

        return $this->data;
    }

    public function getHash() {
        return $this->hash;
    }

    public function &getInfo() {
        $this->requiresUpdate = true;

        return $this->info;
    }

    public function getJSON() {
        return [
            'hash' => $this->hash,
            'finished' => $this->finished,
            'info' => $this->info
        ];
    }

    public function isDestroyed() {
        return $this->destroyed;
    }

    public function isFinished() {
        return $this->finished;
    }

    public function isNew() {
        return $this->new;
    }

    public function setData(array $data) {
        $this->data = $data;
        $this->requiresUpdate = true;
    }

    public function setInfo(array $info) {
        $this->info = $info;
        $this->requiresUpdate = true;
    }

    public function update(EC\MDatabase $db) {
        if (!$this->requiresUpdate && !$this->isNew())
            return true;

        /* Create */
        if ($this->isNew()) {
            if ($this->isDestroyed())
                return true;

            return (new TTasks($db))->update([[
                'Hash' => $this->hash,
                'User_Id' => $this->userId,
                'DateTime' => time(),
                'Finished' => $this->finished,
                'Info' => $this->info,
                'Data' => $this->data
            ]]);
        }

        if (!$this->requiresUpdate)
            return true;

        /* Delete */
        if ($this->isDestroyed()) {
            return (new TTasks($db))->delete_Where([
                [ 'Hash', '=', $this->hash ],
                [ 'User_Id', '=', $this->userId ],
            ]);
        }

        /* Update */
        return (new TTasks($db))->update_Where([
            'Finished' => $this->finished,
            'Info' => $this->info,
            'Data' => $this->data
        ], [
            [ 'Hash', '=', $this->hash ]
        ]);
    }

}
