<?php namespace EC\Files;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HFiles {

    static public function Dir_Create_Safe(string $dirPath, 
            int $permissions = 0777, bool $recursive = false): bool {
        if (file_exists($dirPath)) {
            if (is_dir($dirPath)) {
                return true;
            } else {
                throw new \Exception("Path '{$dirPath}' is not a directory.");
            }
        }

        try {
            $result = mkdir($dirPath, $permissions, $recursive);
        } catch (\Exception $e) {
            if (file_exists($dirPath)) {
                if (is_dir($dirPath)) {
                    return true;
                }
            }

            throw $e;
        }

        return $result;
    }

    static public function Dir_Remove($dir_path) {
        $objects = scandir($dir_path);

        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir_path."/".$object)) {
                    if (!self::Dir_Remove($dir_path."/".$object))
                        return false;
                } else {
                    if (!unlink($dir_path."/".$object))
                        return false;
                }
            }
        }

        return rmdir($dir_path);
    }

}
