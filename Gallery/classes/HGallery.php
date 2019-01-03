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
                'image' => "{$dirUri}/{$fileBaseName}",
                'thumb' => "{$dirUri}_thumb/{$fileBaseName}",
                'full' => "{$dirUri}_full/{$fileBaseName}",
            ];
        }

        return $gallery;
    }

}