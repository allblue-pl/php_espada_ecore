<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HConfig
{

    static private $Properties = null;

    static public function DB_Get(EC\MDatabase $db, $name)
    {
        $table = new TSettings($db);
        $row = $table->row_Where([
            [ 'Name', '=', $name ],
        ]);

        if ($row === null)
            return null;

        return $row['Value'];
    }

    static public function DB_Set(EC\MDatabase $db, $name, $value)
    {
        $table = new TSettings($db);
        return $table->update([[
            'Name' => $name,
            'Value' =>  $value,
        ]]);
    }

    static public function Get($package_name, $property_name, $default_value = null)
    {
        self::Initialize();

        if (!isset(self::$Properties[$package_name]))
            return $default_value;
        if (!isset(self::$Properties[$package_name][$property_name]))
            return $default_value;

        return self::$Properties[$package_name][$property_name];
    }

    static public function GetRequired($package_name, $property_name)
    {
        self::Initialize();

        if (!isset(self::$Properties[$package_name]))
            throw new \Exception("Config property `{$package_name}.{$property_name}`" .
                    " not set.");
        if (!isset(self::$Properties[$package_name][$property_name]))
            throw new \Exception("Config property `{$package_name}.{$property_name}`" .
                    " not set.");

        return self::$Properties[$package_name][$property_name];
    }

    static public function Initialize()
    {
        if (self::$Properties !== null)
            return;

        self::$Properties = [];
        self::RequireConfigFile(self::$Properties);
    }


    // static private function GetPropertyValue($property_array, $property_name,
    //         $required, $default_value = null)
    // {
    //     $property_name_array = explode('.', $property_name);
    //     $value = $property_array;
    //     foreach ($property_name_array as $property_name_part) {
    //         if (!array_key_exists($property_array, $property_name)) {
    //             if ($required) {
    //                 throw new \Exception("Config property `{$package_name}.{$property_name}`" .
    //                         " not set.");
    //             } else
    //                 return $default_value;
    //         }
    //
    //         $value = $value[$property_name_part];
    //     }
    //
    //     return $value;
    // }

    static private function RequireConfigFile()
	{
        $file_path = PATH_DATA . '/Config/config.php';

		if (!file_exists($file_path))
			throw new \Exception('Config file `'.$file_path.'` does not exist.');

		$eConfig = new CConfig_Setter();

		unset($file_path);

		require(PATH_DATA . '/Config/config.php');

        self::$Properties = array_replace_recursive(self::$Properties,
                $eConfig->getProperties());
	}

}
