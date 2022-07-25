<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class HUsers
{

    const Exists_Login  = 1;
    const Exists_Email  = 2;

	const HashRounds_Default = 12;

	const Password_MinCharacters = 6;


	static public function Activate(EC\MDatabase $db, $userId, bool $active)
	{
		return (new TUsers($db))->update_Where([ 
            'Active' => $active 
        ], [
            [ 'Id', '=', $userId ]
        ]);
	}

	static public function ChangePassword(EC\MDatabase $db, $userId,
			$newPassword)
	{
		$newPassword_Hash = self::GetPasswordHash($newPassword);

		return (new TUsers($db))->update_Where(
			[ 'PasswordHash' => $newPassword_Hash ],
			[[ 'Id', '=', $userId ]]);
	}

    static public function CheckEmailHash($email, $emailHash)
	{
		return EC\HHash::CheckPassword($email, $emailHash);
	}

    static public function CheckLoginAndPassword(EC\MDatabase $db, string $type, 
            string $login, string $password)
	{
        $login = mb_strtolower($login);

        $testUsers = self::GetTestUsers();
		foreach ($testUsers as $testUser) {
            if ($testUser['type'] !== $type || $testUser['login'] !== $login)
                continue;


            $authenticated = false;
            if (array_key_exists('passwordHash', $testUser))
                $authenticated = EC\HHash::CheckPassword($password, $testUser['passwordHash']);
            else if (array_key_exists('password', $testUser))
                $authenticated = $testUser['password'] === $password;

            if (!$authenticated)
                continue;

            $user = [];

            $user['id'] = $testUser['id'];
            $user['login'] = $login;
            $user['groups'] = explode(',',
                    str_replace(' ', '', $testUser['groups']));
            $user['permissions'] = HPermissions::Get_FromGroups($user['groups']);
            
            return $user;
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

    static public function Delete(EC\MDatabase $db, $userId)
    {
        return (new TUsers($db))->delete_ById($userId);
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
    //     	return $database->getLastInsertedId();
	// 	else
	// 		return $id;
    // }

    static public function Exists(EC\MDatabase $db, string $type, string $login, 
            $excludedIds = null, &$existingUserId = null)
    {
        $loginHash = self::GetLoginHash($login);

        if ($excludedIds === null)
            $excludedIds = [ -1 ];

		$row = (new TUsers($db))->row_Where([
			[ 'Id', 'NOT IN', $excludedIds ],
			[ 'LoginHash', '=', $loginHash ],
        ]);

		if ($row === null) {
            $existingUserId = null;
            return false;
        }
            
        $existingUserId = $row['Id'];

		return true;
	}

	static public function GetLoginHash($login, $hashRounds = null)
	{
        $hashRounds = $hashRounds === null ? self::GetHashRounds() : $hashRounds;

		return EC\HHash::Get(EC\HHash::Salt(), mb_strtolower($login),
				self::GetHashRounds());
	}

	static public function GetEmailHash($email, $hashRounds = null)
    {
        $hashRounds = $hashRounds === null ? self::GetHashRounds() : $hashRounds;

        return EC\HHash::GetPassword(mb_strtolower($email), self::GetHashRounds());
    }

    static public function GetHashRounds()
    {
        return EC\HConfig::Get('Users', 'hashRounds', 
                self::HashRounds_Default);
    }

    static public function GetPasswordHash($password, $hashRounds = null)
    {
        $hashRounds = $hashRounds === null ? self::GetHashRounds() : $hashRounds;

        return EC\HHash::GetPassword($password, self::GetHashRounds());
    }

    static public function InitSPK(EC\MELibs $eLibs, $userApiUri)
    {
        $eLibs->addTranslations('Users');
        $eLibs->setField('eUsers', [
            'userApiUri' => $userApiUri,
        ]);
    }

    static public function ResetPassword_CreateHash(EC\MDatabase $db, 
            float $userId, string &$hash)
    {
        $hash = EC\HHash::Generate(128);

        (new TResetPasswordHashes($db))->delete_Where([
            [ 'DateTime', '<', time() - EC\HDate::Span_Day ],
        ]);

        return (new TResetPasswordHashes($db))->update([[
            'Id' => null,
            'User_Id' => $userId,
            'DateTime' => time(),
            'Hash' => $hash,
        ]]);
    }

    static public function Update(EC\MDatabase $db, string $type, $id, $login = null, 
            $email = null, $password = null, $groups = null, $active = null)
	{
        $row = [
            'Id' => $id,
            'Type' => $type,
        ];

        if ($login !== null)
            $row['LoginHash'] = self::GetLoginHash($login);

        if ($email !== null)
            $row['EmailHash'] = self::GetEmailHash($email);

		if ($password !== null)
            $row['PasswordHash'] = self::GetPasswordHash($password);
		else if ($row['Id'] !== null) {
			$row_DB = (new TUsers($db))->row_ById($row['Id']);
			$row['PasswordHash'] = $row_DB['PasswordHash'];
		}

        if ($groups !== null)
            $row['Groups'] = $groups;

        if ($active !== null)
            $row['Active'] = $active ? true : false;

		return (new TUsers($db))->update([ $row ]);
    }
    
    static public function ValidateEmail(EC\MDatabase $db, $userType, 
            EC\Forms\CValidator $validator, $fieldName, $login, $userId)
    {
        $excludedIds = [ -1 ];
        if ($userId !== null)
            $excludedIds[] = $userId;

        if (self::Exists($db, $userType, $login, $excludedIds))
            $validator->fieldError($fieldName, EC\HText::_('Users:Errors_UserAlreadyExists'));
    }

}
