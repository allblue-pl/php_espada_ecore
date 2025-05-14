<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HConfig {

    static private $Properties = null;
    static private $DBCache = [];

    static public function DB_Get(EC\MDatabase $db, $name, $defaultValue = null,
            $forUpdate = false)
    {
        if (array_key_exists($name, HConfig::$DBCache))
            return HConfig::$DBCache[$name];

        $table = new TSettings($db);
        $row = $table->row_Where([
            [ 'Name', '=', $name ],
        ], '', $forUpdate);

        if ($row === null) {
            $defaultValues = HConfig::Get('Config', 'DB', []);
            if (array_key_exists($name, $defaultValues)) {
                HConfig::$DBCache[$name] = $defaultValues[$name];
                return $defaultValues[$name];
            }

            HConfig::$DBCache[$name] = $defaultValue;
            return $defaultValue;
        }

        HConfig::$DBCache[$name] = $row['Value'];
        return $row['Value'];
    }

    static public function DB_Set(EC\MDatabase $db, $name, $value)
    {
        $table = new TSettings($db);

        if (!$table->update([[
            'Name' => $name,
            'Value' =>  $value,
                ]]))
            return false;

        HConfig::$DBCache[$name] = $value;
        return true;
    }

    static public function Get($packageName, $propertyName, $default_value = null)
    {
        self::Initialize();

        if (!isset(self::$Properties[$packageName]))
            return $default_value;
        if (!isset(self::$Properties[$packageName][$propertyName]))
            return $default_value;

        return self::$Properties[$packageName][$propertyName];
    }

    static public function GetR($packageName, $propertyName)
    {
        return self::GetRequired($packageName, $propertyName);
    }

    static public function GetRequired($packageName, $propertyName)
    {
        self::Initialize();

        if (!isset(self::$Properties[$packageName]))
            throw new \Exception("Config property `{$packageName}.{$propertyName}`" .
                    " not set.");
        if (!isset(self::$Properties[$packageName][$propertyName]))
            throw new \Exception("Config property `{$packageName}.{$propertyName}`" .
                    " not set.");

        return self::$Properties[$packageName][$propertyName];
    }

    static public function Initialize()
    {
        if (self::$Properties !== null)
            return;

        self::$Properties = [];
        self::RequireConfigFile(self::$Properties);
    }


    // static private function GetPropertyValue($property_array, $propertyName,
    //         $required, $default_value = null)
    // {
    //     $propertyName_array = explode('.', $propertyName);
    //     $value = $property_array;
    //     foreach ($propertyName_array as $propertyName_part) {
    //         if (!array_key_exists($property_array, $propertyName)) {
    //             if ($required) {
    //                 throw new \Exception("Config property `{$packageName}.{$propertyName}`" .
    //                         " not set.");
    //             } else
    //                 return $default_value;
    //         }
    //
    //         $value = $value[$propertyName_part];
    //     }
    //
    //     return $value;
    // }

    static private function RequireConfigFile()
	{
        $file_path = PATH_PRESETS . '/Config/config.php';

		if (!file_exists($file_path))
			throw new \Exception('Config file `'.$file_path.'` does not exist.');

		$eConfig = new CConfig_Setter();

		unset($file_path);

		require(PATH_PRESETS . '/Config/config.php');

        self::$Properties = array_replace_recursive(self::$Properties,
                $eConfig->getProperties());
	}

}
