<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Api\CArgs;
use EC\Api\CResult;
use EC\Api\SApi;
use EC\Text\HText;

class AUsers extends EC\Api\ABasic {

    private $requiredPermissions = null;

    private $db = null;
    private $user = null;

    public function __construct(SApi $site, $args) {
        parent::__construct($site, $args['userType']);

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

    public function action_Activate(EC\Api\CArgs $args) {
        foreach ($this->requiredPermissions as $permission) {
            if (!$this->user->hasPermission($permission))
                return CResult::Failure('Permission denied.');
        }

        $existingActiveUserId = null;
        if (!HUsers::Activate($this->db, $args->id, $args->active, 
                $existingActiveUserId)) {
            if ($existingActiveUserId !== null) {
                return CResult::Failure(HText::_(
                        'Users:Errors_ActiveUserWithLoginAlreadyExists'));
            }

            return CResult::Failure($args->active ?
                    HText::_('Users:Errors_CannotActivateUser') :
                    HText::_('Users:Errors_CannotDeactivateUser'));
        }

        return CResult::Success();
    }

}
