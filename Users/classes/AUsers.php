<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Users,
    EC\Api\CResult, EC\Api\CArgs;

class AUsers extends EC\Api\ABasic
{

    private $requiredPermissions = null;

    private $db = null;
    private $user = null;

    public function __construct(EC\SApi $site, $args)
    {
        parent::__construct($site);

        if (!isset($args['requiredPermissions']))
            throw new \Exception('No `requiredPermissions` specified in' .
                    ' api args.');

        $this->requiredPermissions = $args['requiredPermissions'];

        /* Modules */
        $this->db = $site->m->db;
        $this->user = $site->m->user;

        /* Actions */
        $this->action('activate', 'action_Activate', [
            'id' => true,
            'active' => true,
        ]);
    }

    protected function action_Activate(EC\Api\CArgs $args)
    {
        foreach ($this->requiredPermissions as $permission) {
            if (!$this->user->hasPermission($permission))
                return CResult::Failure('Permission denied.');
        }

        $t_clients = new TUsers($this->db);
        if (!$t_clients->update([[
            'Id' => $args->id,
            'Active' => $args->active,
                ]]))
            return CResult::Failure('Cannot update users.');

        return CResult::Success();
    }

}
