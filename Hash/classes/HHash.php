<?php namespace EC\Hash;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HHash
{

	static public function Get($salt, $string, $hash_rounds = 12)
	{
		$hash = $salt . $string;
		for ($i = 0; $i < $hash_rounds; $i++)
			$hash = hash('sha512', $salt . $hash);

		return $hash;
	}

	static public function GetPassword($password, $hash_rounds = 12)
	{
		$options = [ 'cost' => $hash_rounds ];
		return password_hash($password, PASSWORD_BCRYPT);
	}

	static public function CheckPassword($password, $password_hash)
	{
		return password_verify($password, $password_hash);
	}

	static public function Generate($length)
	{
		$characters = "0123456789" .
			   		  "abcdefghijklmnopqrstuvwxyz" .
			          "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$characters_length = mb_strlen($characters);

		$hash = '';
		for ($i = 0; $i < $length; $i++)
			$hash .= $characters[rand(0, $characters_length - 1)];

		return $hash;
	}

	static public function Salt()
	{
		return EC\HConfig::GetRequired('Hash', 'salt');
	}

}
