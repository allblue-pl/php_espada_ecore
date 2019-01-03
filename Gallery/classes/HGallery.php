<?php namespace EC\Gallery;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HGallery
{

    static public function GetGallery($categoryName, $id)
    {
        $dirUri = EC\HFilesUpload::GetDirUri($categoryName, $id);
        $fileBaseNames = EC\HFilesUpload::GetFileBaseNames($categoryName, $id);

        $gallery = [];
        foreach ($fileBaseNames as $fileBaseName) {
            $gallery[] = [
                'imageUri' => "{$dirUri}/{$fileBaseName}",
                'thumbUri' => "{$dirUri}_thumb/{$fileBaseName}",
                'fullUri' => "{$dirUri}_full/{$fileBaseName}",
            ];
        }

        return $gallery;
    }

}