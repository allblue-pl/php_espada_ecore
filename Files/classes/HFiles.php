<?php namespace EC\Files;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HFiles
{

    static public function Dir_Remove($dir_path)
    {
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
