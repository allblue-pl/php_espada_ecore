<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Api\CArgs;
use EC\Api\CResult;
use EC\Api\SUserApi;
use EC\Database\MDatabase;
use EC\Users\MUser;

class ATasks extends EC\Api\AUserApi {
    private MDatabase $db;
    private MUser $user;

    public function __construct(SUserApi $site) {
        parent::__construct($site);

        /* Modules */
        $this->db = $site->getDB();
        $this->user = $site->getUser();

        /* Actions */
        $this->action('start', 'action_Start', [
            'info' => true
        ]);
        $this->action('status', 'action_Status', [
            'hash' => true,
            'destroyOnFinish' => true
        ]);
    }

    public function action_Start(CArgs $args) {
        if (!$this->user->isLoggedIn())
            return CResult::Failure('Permission denied.');

        $info = json_decode($args->get("info"), true);
        if ($info === null)
            return CResult::Failure('Cannot parse `info` json.');

        $task = HTasks::Create($this->db);
        if ($task === null)
            return CResult::Failure('Cannot start task.');
        $task->setInfo($info);

        return CResult::Success()
            ->add('task', $task->getHash());
    }

    public function action_Status(CArgs $args) {
        if (!$this->user->isLoggedIn())
            return CResult::Failure('Permission denied.');

        $task = HTasks::Get($this->db, $args->get("hash"));
        if ($task === null)
            return CResult::Failure('Task does not exist.');

        $result = CResult::Success()
            ->add('task', $task);

        if ($args->get("destroyOnFinish") && $task->isFinished()) {
            $task->destroy();
            if ($task->update($this->db))
                return CResult::Failure('Cannot update task.');
        }

        return $result;
    }

}
