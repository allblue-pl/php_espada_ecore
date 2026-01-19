<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Api\CArgs;
use EC\Api\CResult;
use EC\Api\SApi;

class ATasks extends EC\Api\ABasic {

    private $db = null;
    private $user = null;

    public function __construct(SApi $site, $args) {
        parent::__construct($site);

        /* Modules */
        $this->db = $site->m->db;
        $this->user = $site->m->user;

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

        $info = json_decode($args->info, true);
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

        $task = HTasks::Get($this->db, $args->hash);
        if ($task === null)
            return CResult::Failure('Task does not exist.');

        $result = CResult::Success()
            ->add('task', $task);

        if ($args->destroyOnFinish && $task->isFinished()) {
            $task->destroy();
            if ($task->update($this->db))
                return CResult::Failure('Cannot update task.');
        }

        return $result;
    }

}
