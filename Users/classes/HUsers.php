<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class HUsers
{

	const HashRounds = 7;

    const Exists_Login  = 1;
    const Exists_Email  = 2;

	const Password_MinCharacters = 6;


	static public function Activate(EC\MDatabase $db, $userId, $active)
	{
		return (new TUsers($db))->update_Where([ 'active' => $active ],
				[ 'id', '=', $userId ]);
	}

	static public function ChangePassword(EC\MDatabase $db, $userId,
			$newPassword)
	{
		$newPassword_Hash = self::GetPasswordHash($newPassword);

		return (new TUsers($db))->update_Where(
			[ 'PasswordHash' => $newPassword_Hash ],
			[[ 'Id', '=', $userId ]]);
	}

	static public function CheckLoginAndPassword(EC\MDatabase $db, $type, $login,
			$password)
	{
		$testUsers = self::GetTestUsers();
		foreach ($testUsers as $testUser) {
            if ($testUser['type'] === $type && $testUser['login'] === $login && 
                    EC\HHash::CheckPassword($password, $testUser['password'])) {

				$user = [];

				$user['id'] = $testUser['id'];
				$user['login'] = $login;
				$user['groups'] = explode(',',
						str_replace(' ', '', $testUser['groups']));
				$user['permissions'] = HPermissions::Get_FromGroups($user['groups']);

				return $user;
			}
		}

		$salt = EC\HHash::Salt();
        $loginHash = self::GetLoginHash($login);

		$row = (new TUsers($db))->row_Where([
            [ 'Type', '=', $type ],
			[ 'LoginHash', '=', $loginHash ],
			[ 'Active', '=', true ]
		]);

		if ($row === null)
			return null;

		if (!self::CheckPasswordHash($password, $row['PasswordHash']))
			return null;

		return [
			'id' => $row['Id'],
			'login' => $login,
			'groups' => $row['Groups'],
			'permissions' => $row['Groups_Permissions']
		];
	}

	static public function CheckPasswordHash($password, $password_hash)
	{
		return EC\HHash::CheckPassword($password, $password_hash);
	}

	static public function CheckPasswordStrength($password)
	{
		if(strlen($password) < self::Password_MinCharacters)
			return false;
		if (!preg_match('#[A-Z]#', $password))
			return false;
		if (!preg_match('#[a-z]#', $password))
			return false;
		if (!preg_match('#[0-9]#', $password))
			return false;
		if (!preg_match('#[a-zA-Z!@\#$%\^&\*]+#', $password))
			return false;

		return true;
	}

	static public function Get(EC\MDatabase $db, $userId)
	{
		return (new TUsers($db))->row_ById($userId);
	}

	static public function GetTestUsers()
	{
		return EC\HConfig::Get('Users', 'testUsers', []);
	}

	//
    // static public function Update(EC\MDatabase $database,
	// 		$id, $login, $email, $password, $groups, $active)
    // {
	// 	$loginHash = self::GetLoginHash($login);
    //     $email_hash = self::GetEMailHash($email);
    //     $groups_string = implode(',', $groups);
	//
	// 	$db_id = $database->escapeInt($id);
    //     $db_login_hash = $database->escapeString($loginHash);
    //     $db_email_hash = $database->escapeString($email_hash);
    //     $db_groups_string = $database->escapeString($groups_string);
    //     $db_active = $database->escapeBool($active);
	//
	// 	if ($password !== null) {
	// 		$password_hash = self::GetPasswordHash($password);
	// 		$db_password_hash = $database->escapeString($password_hash);
	// 	}
	//
    //     $query = 'INSERT INTO Users_Users' .
    //              ' (id, loginHash, emailHash, groups, active';
	//
	// 	if ($password !== null)
	// 		$query .= ', passwordHash';
	//
	// 	$query .= ')' .
	// 		" VALUES({$db_id}, {$db_login_hash}, {$db_email_hash}" .
	// 		", {$db_groups_string}, {$db_active}";
	//
	// 	if ($password !== null)
	// 		$query .= ", {$db_password_hash}";
	//
	// 	$query .= ')' .
	// 		' ON DUPLICATE KEY UPDATE' .
	// 		' loginHash = VALUES(loginHash)' .
	// 		', emailHash = VALUES(emailHash)' .
	// 		', groups = VALUES(groups)' .
	// 		', active = VALUES(active)';
	//
	// 	if ($password !== null)
	// 		$query .= ', passwordHash = VALUES(passwordHash)';
	//
    //     if (!$database->query_Execute($query))
    //         return 0;
	//
	// 	if ($id === null)
    //     	return $database->getInsertedId();
	// 	else
	// 		return $id;
    // }

	static public function Exists(EC\MDatabase $db, $type, $login, $excluded_ids = [ -1 ]) {
		$loginHash = self::GetLoginHash($login);

		$row = (new TUsers($db))->row_Where([
			[ 'Id', 'NOT IN', $excluded_ids ],
			[ 'LoginHash', '=', $loginHash ],
		]);

		if ($row === null)
			return false;

		return true;
	}

	static public function GetLoginHash($login)
	{
		return EC\HHash::Get(EC\HHash::Salt(), mb_strtolower($login),
				self::HashRounds);
	}

	static public function GetEmailHash($email)
    {
        return EC\HHash::GetPassword(mb_strtolower($email), self::HashRounds);
    }

    static public function GetPasswordHash($password)
    {
        return EC\HHash::GetPassword($password, self::HashRounds);
    }

    static public function InitSPK(EC\MELibs $eLibs, $userApiUri)
    {
        $eLibs->addTranslations('Users');
        $eLibs->setField('eUsers', [
            'userApiUri' => $userApiUri,
        ]);
    }

	static public function Update(EC\MDatabase $db, $row)
	{
		if (array_key_exists('Login', $row)) {
			$row['LoginHash'] = self::GetLoginHash($row['Login']);
			unset($row['Login']);
		}

		if (array_key_exists('Email', $row)) {
			$row['EmailHash'] = self::GetEmailHash($row['Email']);
			unset($row['Email']);
		}

		if (array_key_exists('Password', $row)) {
			if ($row['Password'] !== null)
				$row['PasswordHash'] = EC\HHash::GetPassword($row['Password']);

			unset($row['Password']);
		}

		return (new TUsers($db))->update([ $row ]);
    }
    
    static public function ValidateEmail(EC\MDatabase $db, $userType, 
            EC\Forms\CValidator $validator, $fieldName, $login, $userId)
    {
        $excludedIds = [ -1 ];
        if ($userId !== null)
            $excludedIds[] = $userId;

        if (self::Exists($db, $userType, $login, $excludedIds))
            $validator->fieldError($fieldName, EC\HText::_('Users:errors_UserAlreadyExists'));
    }

}
