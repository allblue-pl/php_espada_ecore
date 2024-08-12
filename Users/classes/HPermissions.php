<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HPermissions
{

    static public function Get_FromGroups($groups)
    {
        $permissions = [];
        $config_groups = EC\HConfig::Get('Users', 'groups', []);

        foreach ($groups as $group_name) {
            if (!array_key_exists($group_name, $config_groups))
                throw new \Exception("Group `{$group_name}` does not exist.");

            $permissions = array_merge($permissions, $config_groups[$group_name]);
        }

        return $permissions;
    }

    // static private function Get_ByGroup($raw_groups, &$groups,
	// 		$group_name, $stack)
	// {
	// 	if (isset($groups[$group_name]))
	// 		return $groups[$group_name];

	// 	if (!array_key_exists($group_name, $raw_groups)) {
	// 		throw new \Exception("No `{$group_name}` in `users_Groups`" .
	// 				' in Config.');
	// 	}

	// 	$permissions = self::ParsePermissions($raw_groups[$group_name]);

	// 	$parsed_name = explode(':', $group_name);

	// 	if (count($parsed_name) === 1) {
	// 		$groups[$parsed_name[0]] = $permissions;
	// 		return $permissions;
	// 	}

	// 	$extend_group_names =
	// 			explode(',', str_replace(' ', '', $parsed_name[1]));

	// 	foreach ($extend_group_names as $extend_group_name) {
	// 		if ($extend_group_name === '')
	// 			continue;

	// 		if (in_array($group_name, $stack)) {
	// 			throw new \Exception('Circular dependency in `users_Groups`' .
	// 					' in Config.');
	// 		}

	// 		$extend_permissions = self::GetGroupPermissions($raw_groups,
	// 				$groups, $extend_group_name, $stack);

	// 		$stack[] = $extend_group_name;
	// 		$permissions = array_merge($permissions, $extend_permissions);
	// 	}

	// 	$groups[$parsed_name[0]] = $permissions;
	// 	return $permissions;
	// }

    static public function Parse($permissions_string)
	{
		$t_permissions = explode(',', str_replace(' ', '',
				$permissions_string));

		$permissions = [];
		foreach ($t_permissions as $permission)
			if ($permission !== '')
				$permissions[] = $permission;

		return $permissions;
	}

}
