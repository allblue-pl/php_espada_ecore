<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Users;

class AUser extends EC\Api\ABasic
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
        $this->action('check', 'action_Check');

        $this->action('log-in', 'action_LogIn', [
            'login' => true,
            'password' => true
        ]);

        $this->action('log-out', 'action_LogOut');

        $this->action('change-password', 'action_ChangePassword', [
            'oldPassword' => true,
            'newPassword' => true
        ]);
    }

    protected function action_ChangePassword(EC\Api\CArgs $args)
    {
        $db = $this->db;
        $user = $this->user;

        if (!$user->isLoggedIn())
            return EC\Api\CResult::Failure('Not logged in.');

        $user_id = $user->getId();
        $user_login = $user->getLogin();

        if (!HUsers::CheckLoginAndPassword($db,
                $user_login, $args->oldPassword)) {
            $result = EC\Api\CResult::Failure();
            $result->add('error', [
                'type' => 'wrongPassword',
                'message' => EC\HText::_('Users:errors_WrongPassword')
            ]);

            return $result;
        }

        if (!HUsers::CheckPasswordStrength($args->newPassword)) {
            $result = EC\Api\CResult::Failure();
            $result->add('error', [
                'type' => 'wrongPasswordFormat',
                'message' => EC\HText::_('Users:errors_WrongPasswordFormat')
            ]);

            return $result;
        }

        if (!HUsers::ChangePassword($db, $user_id,
                $args->newPassword))
            return EC\Api\CResult::Error();

        return EC\Api\CResult::Success();
    }

    protected function action_Check()
    {
        return EC\Api\CResult::Success()
            ->add('isLoggedIn', $this->user->isLoggedIn());
    }

    protected function action_LogIn(EC\Api\CArgs $args)
    {
        $login = $args->login;
        $password = $args->password;

        $db = $this->db;
        $user = $this->user;

        if ($user->isLoggedIn()) {
			$result = EC\Api\CResult::Failure('Log out first.');
			$result->add('login', $user->getLogin());

			return $result;
		}

		$user_info = EC\HUsers::CheckLoginAndPassword($db, $login,
                $password);

		if ($user_info === null) {
			return EC\Api\CResult::Failure('`login` and `password`' .
									  ' do not match.`');
		}

		$user_permissions = $user_info['permissions'];

		foreach ($this->requiredPermissions as $permission) {
			if (!in_array($permission, $user_permissions)) {
				return EC\Api\CResult::Failure('`login` and `password`' .
										  ' do not match.`')
                    ->debug('Permission denied.');
			}
		}

		$user->startSession($user_info['id'], $login);

		return EC\Api\CResult::Success();
    }

    protected function action_LogOut()
    {
        $user = $this->user;

        if ($user->isLoggedIn()) {
			$user->destroy();

			return EC\Api\CResult::Success();
		}

		return EC\Api\CResult::Failure('Not logged in.');
    }

}
