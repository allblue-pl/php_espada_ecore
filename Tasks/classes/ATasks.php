<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Api\CArgs, EC\Api\CResult;

class ATasks extends EC\Api\ABasic
{

    private $db = null;
    private $user = null;

    public function __construct(EC\SApi $site, $args)
    {
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

    public function action_Start(CArgs $args)
    {
        if (!$this->user->isLoggedIn())
            return CResult::Failure('Permission denied.');

        $info = json_decode($args->info, true);
        if ($info === null)
            return CResult::Failure('Cannot parse `info` json.');

        $task = HTasks::Start($this->db, $info);
        if ($task === null)
            return CResult::Failure('Cannot start task.');

        return CResult::Success()
            ->add('task', $task_hash);
    }

    public function action_Status(CArgs $args)
    {
        if (!$this->user->isLoggedIn())
            return CResult::Failure('Permission denied.');

        $task = HTasks::Get($this->db, $args->hash);
        if ($task === null)
            return CResult::Failure('Task does not exist.');

        $result = CResult::Success()
            ->add('task', $task);

        if ($args->destroyOnFinish && $task['finished']) {
            if (!HTasks::Destroy($this->db, $task['hash']))
                $result->add('error', 'Cannot destroy task.');
        }

        return $result;
    }

}
