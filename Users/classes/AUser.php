<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, 
    EC\Users,
    EC\Api\CResult, EC\Api\CArgs;

class AUser extends EC\Api\ABasic
{

    private $userType = null;
    private $requiredPermissions = null;

    private $db = null;
    private $user = null;

    public function __construct(EC\SApi $site, $args)
    {
        parent::__construct($site, array_key_exists('userType', $args) ? 
                $args['userType'] : 'Default');

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
            'Login' => true,
            'Password' => true
        ]);

        $this->action('log-out', 'action_LogOut');

        $this->action('change-password', 'action_ChangePassword', [
            'Password' => true,
            'NewPassword' => true
        ]);

        if (EDEBUG) {
            $this->action('hash', 'action_Hash', [
                'Password' => true,
                'HashRounds' => false,
            ]);
        }
    }

    protected function action_ChangePassword(CArgs $args)
    {
        $db = $this->db;
        $user = $this->user;

        if (!$user->isLoggedIn())
            return CResult::Failure('Not logged in.');

        $userId = $user->getId();
        $userLogin = $user->getLogin();

        if (!HUsers::CheckLoginAndPassword($db, $this->user->getType(),
                $userLogin, $args->Password))
            return CResult::Failure(EC\HText::_('Users:errors_WrongPassword'));

        if (!HUsers::CheckPasswordStrength($args->NewPassword))
            return CResult::Failure(EC\HText::_('Users:errors_WrongPasswordFormat'));

        if (!HUsers::ChangePassword($db, $userId,
                $args->NewPassword))
            return CResult::Error();

        return CResult::Success(EC\HText::_('Users:successes_PasswordChanged'));
    }

    protected function action_Check()
    {
        return CResult::Success()
            ->add('isLoggedIn', $this->user->isLoggedIn());
    }

    protected function action_Hash(CArgs $args)
    {
        $hash = null;
        if (isset($args->HashRounds))
            $hash = EC\HHash::GetPassword($args->Password, $args->HashRounds);
        else
            $hash = EC\HHash::GetPassword($args->Password);

        return CResult::Success()
            ->add('hash', $hash);
    }

    protected function action_LogIn(CArgs $args)
    {
        $login = $args->Login;
        $password = $args->Password;

        $db = $this->db;
        $user = $this->user;

        if ($user->isLoggedIn()) {
			$result = CResult::Failure('Users:errors_LogOutFirst');
			$result
                ->add('user', [
                    'login' => $this->user->getLogin(),
                    'permissions' => $this->user->getPermissions(),
                ]);

			return $result;
        }

        $userInfo = EC\HUsers::CheckLoginAndPassword($db, $user->getType(), 
                $login, $password);

		if ($userInfo === null) {
			return CResult::Failure(EC\HText::_('Users:errors_WrongLoginOrPassword'))
                ->add('user', [
                    'login' => null,
                    'permissions' => [],
                ]);
		}

		$userPermissions = $userInfo['permissions'];

		foreach ($this->requiredPermissions as $permission) {
			if (!in_array($permission, $userPermissions)) {
				return CResult::Failure(EC\HText::_('Users:errors_WrongLoginOrPassword'))
                    ->add('user', [
                        'login' => null,
                        'permissions' => [],
                    ])
                    ->debug('Permission denied.');
			}
		}

		$user->startSession($userInfo['id'], $login);

		return CResult::Success()
            ->add('user', [
                'login' => $this->user->getLogin(),
                'permissions' => $this->user->getPermissions(),
            ]);
    }

    protected function action_LogOut()
    {
        $user = $this->user;

        if ($user->isLoggedIn()) {
			$user->destroy();

			return CResult::Success();
		}

		return CResult::Failure('Not logged in.');
    }

}
