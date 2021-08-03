<?php namespace EC\Gallery;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HGallery
{

    static public function GetGallery($categoryName, $id, $hasThumbs = true,
            $hasFull = true)
    {
        $dirUri = EC\HFilesUpload::GetDirUri($categoryName, $id);
        $fileBaseNames = EC\HFilesUpload::GetFileBaseNames($categoryName, $id);

        $gallery = [];
        foreach ($fileBaseNames as $fileBaseName) {
            $gallery[] = [
                'imageUri' => "{$dirUri}/{$fileBaseName}",
                'thumbUri' => "{$dirUri}" . ($hasThumbs ? "_thumb" : "") . "/{$fileBaseName}",
                'fullUri' => "{$dirUri}" . ($hasFull ? "_full" : "") . "/{$fileBaseName}",
            ];
        }

        return $gallery;
    }

    static public function Init(E\Site $site)
    {
        $site->addL('postBody', E\Layout::_('Gallery:gallery'));
    }

}