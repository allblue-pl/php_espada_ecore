<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Users;
use EC\Api\CArgs;
use EC\Api\CResult;
use EC\Api\SApi;
use EC\Api\SUserApi;
use EC\Config\HConfig;
use EC\Database\MDatabase;
use EC\Date\HDate;
use EC\Hash\HHash;
use EC\Mailer\HMailer;
use EC\Text\HText;

class AUser extends EC\Api\AUser {
    private array $requiredPermissions;

    private MDatabase $db;
    private MUser $user;

    public function __construct(SUserApi $site, array $args) {
        parent::__construct($site, array_key_exists('requiredPermissions', $args) ? 
                $args['requiredPermissions'] : []);

        if (!isset($args['requiredPermissions']))
            throw new \Exception('No `requiredPermissions` specified in' .
                    ' api args.');

        $this->requiredPermissions = $args['requiredPermissions'];

        /* Modules */
        $this->db = $site->getDB();
        $this->user = $site->getUser();

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

    protected function action_ChangePassword(CArgs $args) {
        $db = $this->db;
        $user = $this->user;

        if (!$user->isLoggedIn())
            return CResult::Failure('Not logged in.');

        $userId = $user->getId();
        $userLogin = $user->getLogin();

        $logInError = null;
        if (!HUsers::CheckLoginAndPassword($db, $this->user->getType(),
                $userLogin, $args->get("password"), $logInError)) {
            $errorMessage = null;
            if ($logInError === HUsers::LogInError_UserDoesNotExist)
                $errorMessage = HText::_('Users:Errors_UserDoesNotExist');
            else if ($logInError === HUsers::LogInError_UserNotActive)
                $errorMessage = HText::_('Users:Errors_UserNotActive');
            else
                $errorMessage = HText::_('Users:Errors_WrongLoginOrPassword');

            return CResult::Failure($errorMessage);
        }

        if (!HUsers::CheckPasswordStrength($args->get("newPassword")))
            return CResult::Failure(HText::_('Users:Errors_WrongPasswordFormat'));

        if (!HUsers::ChangePassword($db, $userId,
                $args->get("newPassword")))
            return CResult::Error();

        return CResult::Success(HText::_('Users:Successes_PasswordChanged'));
    }

    protected function action_Check() {
        return CResult::Success()
            ->add('isLoggedIn', $this->user->isLoggedIn());
    }

    protected function action_Hash(CArgs $args) {
        $hash = null;
        if ($args->isset("hashRounds"))
            $hash = HHash::GetPassword($args->get("password"), $args->get("hashRounds"));
        else
            $hash = HHash::GetPassword($args->get("password"));

        return CResult::Success()
            ->add('hash', $hash);
    }

    protected function action_LogIn(CArgs $args) {
        $login = $args->get("login");
        $password = $args->get("password");

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

        $logInError = null;
        $userInfo = HUsers::CheckLoginAndPassword($db, $user->getType(), 
                $login, $password, $logInError);

		if ($userInfo === null) {
            $errorMessage = null;
            if ($logInError === HUsers::LogInError_UserDoesNotExist)
                $errorMessage = HText::_('Users:Errors_UserDoesNotExist');
            else if ($logInError === HUsers::LogInError_UserNotActive)
                $errorMessage = HText::_('Users:Errors_UserNotActive');
            else
                $errorMessage = HText::_('Users:Errors_WrongLoginOrPassword');

			return CResult::Failure($errorMessage)
                ->add('user', [
                    'login' => null,
                    'permissions' => [],
                ]);
		}

		$userPermissions = $userInfo['permissions'];
		foreach ($this->requiredPermissions as $permission) {
			if (!in_array($permission, $userPermissions)) {
				return CResult::Failure(HText::_('Users:Errors_WrongLoginOrPassword'))
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

    protected function action_LogOut() {
        $user = $this->user;

        if ($user->isLoggedIn()) {
			$user->destroy();

			return CResult::Success();
		}

		return CResult::Failure('Not logged in.');
    }

    protected function action_RemindPassword(CArgs $args) {
        $args->get("set")("email", trim(mb_strtolower($args->get("email"))));

        if ($args->get("login") === '')
            return CResult::Failure(HText::_('Users:Errors_LoginCannotBeEmpty'));
        if ($args->get("email") === '')
            return CResult::Failure(HText::_('Users:Errors_EmailCannotBeEmpty'));

        $row = (new TUsers($this->db))->row_Where([
            [ 'LoginHash', '=', HUsers::GetLoginHash($args->get("login")) ],
        ]);

        if ($row === null) {
            return CResult::Failure(HText::_('Users:Errors_LoginDoesNotExist', [
                'Login' => $args->get("login"),
            ]));
        }

        if (!HUsers::CheckEmailHash($args->get("email"), $row['EmailHash'])) {
            return CResult::Failure(HText::_('Users:Errors_EmailDoesNotMatchLogin', [
                'Login' => $args->get("login"),
                'Email' => $args->get("email"),
            ]));
        }

        $hash = '';
        if (!HUsers::ResetPassword_CreateHash($this->db, $row['Id'], $hash))
            return CResult::Failure(HText::_('Users:Errors_CannotCreateResetPasswordHash'));

        $link = HConfig::GetRequired('Users', 'uris')['resetPassword'] . 
                $hash;

        $mail = HMailer::NewMail($args->get("email"), $args->get("login"));

        $mail->setSubject(HText::_('Users:mails.ResetPassword_Subject', [
            'title' => HConfig::GetRequired('Config', 'title'),
        ]));
        $mail->setText(HText::_('Users:mails.ResetPassword_Text', [
            'title' => HConfig::GetRequired('Config', 'title'),
            'login' => $args->get("login"),
            'link' => $link,
        ]));
        $mail->setHtml(HText::_('Users:mails.ResetPassword_Html', [
            'title' => HConfig::GetRequired('Config', 'title'),
            'login' => $args->get("login"),
            'link' => $link,
        ]));

        if (!$mail->send()) {
            $error = $mail->getError();
            return CResult::Failure(HText::_('Users:Errors_CannotSendEmail'))
                ->debug($error);
        }

        return CResult::Success(HText::_('Users:Successes_PasswordResetLinkSent'))
            ->debug($link)
            ->debug($hash);
    }

    protected function action_ResetPassword(CArgs $args) {
        $rResetPasswordHash = (new TResetPasswordHashes($this->db))
                ->row_Where([
            [ 'Hash', '=', $args->get("resetPasswordHash") ],
        ], 'ORDER BY DateTime DESC');

        if ($rResetPasswordHash === null)
            return CResult::Failure(HText::_('Users:Errors_RecoveryHashDoesNotExist'));

        if ($rResetPasswordHash['DateTime'] < time() - HDate::Span_Day)
            return CResult::Failure(HText::_('Users:Errors_RecoveryHashExpired'));

        if (!HUsers::CheckPasswordStrength($args->get("newPassword")))
            return CResult::Failure(HText::_('Users:Errors_WrongPasswordFormat'));

        if (!HUsers::ChangePassword($this->db, $rResetPasswordHash['User_Id'], 
                $args->get("newPassword")))
            return CResult::Failure(HText::_('Users:Errors_CannotResetPassword'));

        (new EC\Users\TResetPasswordHashes($this->db))->delete_Where([
            [ 'User_Id', '=', $rResetPasswordHash['User_Id'] ],
        ]);

        return CResult::Success(HText::_('Users:Successes_PasswordChanged'));
    }

}
