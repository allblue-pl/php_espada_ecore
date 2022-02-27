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
            'login' => true,
            'password' => true
        ]);
        $this->action('log-out', 'action_LogOut');
        $this->action('change-password', 'action_ChangePassword', [
            'password' => true,
            'newPassword' => true
        ]);
        $this->action('remind-password', 'action_RemindPassword', [
            'login' => true,
            'email' => true,
        ]);
        $this->action('reset-password', 'action_ResetPassword', [
            'resetPasswordHash' => true,
            'newPassword' => true,
        ]);

        if (EDEBUG) {
            $this->action('hash', 'action_Hash', [
                'password' => true,
                'hashRounds' => false,
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
                $userLogin, $args->password))
            return CResult::Failure(EC\HText::_('Users:Errors_WrongPassword'));

        if (!HUsers::CheckPasswordStrength($args->newPassword))
            return CResult::Failure(EC\HText::_('Users:Errors_WrongPasswordFormat'));

        if (!HUsers::ChangePassword($db, $userId,
                $args->newPassword))
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
        $login = $args->login;
        $password = $args->Password;

        $db = $this->db;
        $user = $this->user;

        if ($user->isLoggedIn()) {
			$result = CResult::Failure('Users:Errors_LogOutFirst');
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
			return CResult::Failure(EC\HText::_('Users:Errors_WrongLoginOrPassword'))
                ->add('user', [
                    'login' => null,
                    'permissions' => [],
                ]);
		}

		$userPermissions = $userInfo['permissions'];

		foreach ($this->requiredPermissions as $permission) {
			if (!in_array($permission, $userPermissions)) {
				return CResult::Failure(EC\HText::_('Users:Errors_WrongLoginOrPassword'))
                    ->add('user', [
                        'login' => null,
                        'permissions' => [],
                    ])
                    ->debug('Permission denied. Required permission: ' . $permission);
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

    protected function action_RemindPassword(CArgs $args)
    {
        $args->email = trim(mb_strtolower($args->email));

        if ($args->login === '')
            return CResult::Failure('Users:Errors_LoginCannotBeEmpty');
        if ($args->email === '')
            return CResult::Failure('Users:Errors_EmailCannotBeEmpty');

        $row = (new TUsers($this->db))->row_Where([
            [ 'LoginHash', '=', HUsers::GetLoginHash($args->login) ],
        ]);

        if ($row === null) {
            return CResult::Failure('Users:Errors_LoginDoesNotExist', [
                'Login' => $args->login,
            ]);
        }

        if (!HUsers::CheckEmailHash($args->email, $row['EmailHash'])) {
            return CResult::Failure('Users:Errors_EmailDoesNotMatchLogin', [
                'Login' => $args->login,
                'Email' => $args->email,
            ]);
        }

        $hash = '';
        if (!EC\HUsers::ResetPassword_CreateHash($this->db, $row['Id'], $hash))
            return CResult::Failure('Users:Errors_CannontCreateResetPasswordHash');

        $link = EC\HConfig::GetRequired('Users', 'uris')['resetPassword'] . 
                $hash;

        $mail = EC\HMailer::NewMail($args->email, $args->login);

        $mail->setSubject(EC\HText::_('Users:Mails_ResetPassword_Subject'));
        $mail->setText(EC\HText::_('Users:Mails_ResetPassword_Text'));
        $mail->setHtml(EC\HText::_('Users:Mails_ResetPassword_Html'));

        if (!$mail->send()) {
            $error = $mail->getError();
            return CResult::Failure('Users:Errors_CannotSendEmail')
                ->debug($error);
        }

        return CResult::Success(EC\HText::_('Sys:successes_PasswordResetLinkSent'))
            ->debug($link)
            ->debug($hash);
    }

    protected function action_ResetPassword(CArgs $args)
    {
        $rResetPasswordHash = (new TResetPasswordHashes($this->db))
                ->row_Where([
            [ 'Hash', '=', $args->resetPasswordHash ],
        ], 'ORDER BY DateTime DESC');

        if ($rResetPasswordHash === null)
            return CResult::Failure(EC\HText::_('Users:Errors_RecoveryHashDoesNotExist'));

        if ($rResetPasswordHash['DateTime'] < time() - EC\HDate::Span_Day)
            return CResult::Failure(EC\HText::_('User:Errors_RecoveryHashExpired'));

        if (!HUsers::CheckPasswordStrength($args->newPassword))
            return CResult::Failure(EC\HText::_('Users:Errors_WrongPasswordFormat'));

        if (!EC\HUsers::ChangePassword($this->db, $rResetPasswordHash['User_Id'], 
                $args->newPassword))
            return CResult::Failure('Users:Errors_CannotResetPassword');

        (new EC\Users\TResetPasswordHashes($this->db))->delete_Where([
            [ 'User_Id', '=', $rResetPasswordHash['User_Id'] ],
        ]);

        return CResult::Success('Users:Successes_PasswordChanged');
    }

}
