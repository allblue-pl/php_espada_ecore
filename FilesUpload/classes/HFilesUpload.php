<?php namespace EC\FilesUpload;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HFilesUpload
{

    static public function GetCategory($categoryName)
    {
        $categories = EC\HConfig::GetRequired('FilesUpload', 'categories');
        if (!array_key_exists($categoryName, $categories))
            throw new \Exception("FilesUpload category '{$categoryName}' does not exist.");

        return $categories[$categoryName];
    }

    static public function GetDirMediaPath($categoryName, $id)
    {
        $category = HFilesUpload::GetCategory($categoryName);

        return $category['multiple'] ?
                "{$category['alias']}-{$id}" :
                mb_substr($category['alias'], 0, mb_strrpos($category['alias'], '/'));
    }

    static public function GetDirUri($categoryName, $id)
    {
        return E\Uri::Media('FilesUpload', self::GetDirMediaPath($categoryName, $id));
    }

    static public function GetFileBaseNames($categoryName, $id)
    {
        $category = HFilesUpload::GetCategory($categoryName);

        $dirMediaPath = HFilesUpload::GetDirMediaPath($categoryName, $id);
        $dirPath = E\Path::Media('FilesUpload', $dirMediaPath);

        if (!file_exists($dirPath))
            return [];

        $files = array_filter(array_diff(scandir($dirPath), [ '.', '..' ]), 
                function($file) use ($category, $id, $dirPath) {
            if (is_dir("{$dirPath}/$file"))
                    return false;

            if ($category['multiple'])
                return true;
            
            $fileName = mb_substr($category['alias'], mb_strrpos($category['alias'], 
                    '/') + 1) . "-{$id}";

            return pathinfo($file, PATHINFO_FILENAME) === $fileName;
        });

        return array_values($files);
    }

    static public function GetFilePaths($categoryName, $id)
    {
        $dirMediaPath = HFilesUpload::GetDirMediaPath($categoryName, $id);
        $fileBaseNames = self::GetFileBaseNames($categoryName, $id);

        $files = [];
        foreach ($fileBaseNames as &$fileBaseName)
            $files[] = E\Path::Media('FilesUpload', "{$dirMediaPath}/{$fileBaseName}");

        return array_values($files);
    }

    static public function GetFileUris($categoryName, $id)
    {
        $dirMediaPath = HFilesUpload::GetDirMediaPath($categoryName, $id);
        $fileBaseNames = self::GetFileBaseNames($categoryName, $id);

        $files = [];
        foreach ($fileBaseNames as &$fileBaseName)
            $files[] = E\Uri::Media('FilesUpload', "{$dirMediaPath}/{$fileBaseName}");

        return array_values($files);
    }

    static public function GetFileUris_Single($categoryName, $id)
    {
        $fileUris = self::GetFileUris($categoryName, $id);
        if (count($fileUris) === 0)
            return null;

        return $fileUris[0];
    }

    static public function ExistsCategory($categoryName)
    {
        $categories = EC\HConfig::GetRequired('FilesUpload', 'categories');

        return array_key_exists($categoryName, $categories);
    }

    static public function Init(EC\MELibs $eLibs, $apiUri, array $overrides = [])
    {
        $field = array_merge_recursive([
            'apiUri' => $apiUri,
            'categories' => EC\HConfig::GetRequired('FilesUpload', 'categories'),
            'uris' => [
                'file' => E\Uri::File('FilesUpload:images/file.jpg'),
                'loading' => E\Uri::File('FilesUpload:images/loading.gif'),
            ],
            'texts' => EC\HText::GetTranslations('FilesUpload:spk')->getArray(),
        ], $overrides);

        $eLibs->setField('eFilesUpload', $field);
    }

}