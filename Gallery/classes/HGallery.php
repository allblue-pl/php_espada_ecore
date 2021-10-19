<?php namespace EC\Gallery;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HGallery
{

    static public function GetGallery($categoryName, $id, $hasThumbs = true,
            $hasFull = true)
    {
        $category = EC\HFilesUpload::GetCategory($categoryName);
        $fileUris = EC\HFilesUpload::GetFileUris($categoryName, $id);
        // $fileUris = [];
        // foreach ([ '$default', 'thumbnail', 'full' ] as $sizeName) {
        //     $fileUris[$sizeName] = [];
        //     if (array_key_exists($sizeName, $category['sizes'])) {
        //         $fileUris[$sizeName] = EC\HFilesUpload::GetFileUris($categoryName, 
        //                 $id, $sizeName);
        //     }
        // }

        $gallery = [];
        foreach ($fileUris as $fileUri) {
            $gallery[] = [
                'imageUri' => $fileUri,
                'thumbUri' => $fileUri,
                'fullUri' => $fileUri,
            ];
        }

        return $gallery;
    }

    static public function Init(E\Site $site, $cspHash = null)
    {
        $site->addL('postBody', E\Layout::_('Gallery:gallery'));
        $site->addL('postBodyInit', new EC\Basic\LScript("
            new EGallery.Class();
        ", $cspHash));
    }

}