<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class HUsers
{

	const HashRounds = 7;

    const Exists_Login  = 1;
    const Exists_Email  = 2;

	const Password_MinCharacters = 6;


	static public function Activate(EC\MDatabase $db, $user_id, $active)
	{
		return (new TUsers($db))->update_Where([ 'active' => $active ],
				[ 'id', '=', $user_id ]);
	}

	static public function ChangePassword(EC\MDatabase $db, $user_id,
			$new_password)
	{
		$new_password_hash = self::GetPasswordHash($new_password);

		return (new TUsers($db))->update_Where(
			[ 'PasswordHash' => $new_password_hash ],
			[[ 'Id', '=', $user_id ]]);
	}

	static public function CheckLoginAndPassword(EC\MDatabase $db, $login,
			$password)
	{
		$test_users = self::GetTestUsers();
		foreach ($test_users as $test_user) {
			if ($test_user['login'] === $login &&
				$test_user['password'] === $password) {

				$user = [];

				$user['id'] = $test_user['id'];
				$user['login'] = $login;
				$user['groups'] = explode(',',
						str_replace(' ', '', $test_user['groups']));
				$user['permissions'] = HPermissions::Get_FromGroups($user['groups']);

				return $user;
			}
		}

		$salt = EC\HHash::Salt();
        $login_hash = self::GetLoginHash($login);

		$row = (new TUsers($db))->row_Where([
			[ 'LoginHash', '=', $login_hash ],
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

	static public function Get(EC\MDatabase $db, $user_id)
	{
		return (new TUsers($db))->row_ById($user_id);
	}

	static public function GetTestUsers()
	{
		return EC\HConfig::Get('Users', 'testUsers', []);
	}

	//
    // static public function Update(EC\MDatabase $database,
	// 		$id, $login, $email, $password, $groups, $active)
    // {
	// 	$login_hash = self::GetLoginHash($login);
    //     $email_hash = self::GetEMailHash($email);
    //     $groups_string = implode(',', $groups);
	//
	// 	$db_id = $database->escapeInt($id);
    //     $db_login_hash = $database->escapeString($login_hash);
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

	static public function Exists(EC\MDatabase $db, $login, $excluded_ids = [ -1 ]) {
		$login_hash = self::GetLoginHash($login);

		$row = (new TUsers($db))->row_Where([
			[ 'Id', 'NOT IN', $excluded_ids ],
			[ 'LoginHash', '=', $login_hash ],
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

}
