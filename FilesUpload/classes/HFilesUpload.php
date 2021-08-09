<?php namespace EC\FilesUpload;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HFilesUpload
{

    static public function Copy($file, $fileMediaPath)
    {
        $filePath = E\Path::Media('FilesUpload', $fileMediaPath);
        if (!file_exists(dirname($filePath)))
            mkdir(dirname($filePath), 0777, true);

        return copy($file['tmp_name'], $filePath);
    }

    static public function DeleteFiles($categoryName, $id)
    {
        $category = HFilesUpload::GetCategory($categoryName);

        $filePaths = self::GetFilePaths($categoryName, $id);
        foreach ($filePaths as $filePath)
            unlink($filePath);

        if ($category['multiple']) {
            $dirPath = E\Path::Media('FilesUpload', self::GetDirMediaPath($categoryName, $id));
            if (file_exists($dirPath)) {
                if (is_dir($dirPath))
                    rmdir($dirPath);
            }
        }
    }

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

    static public function GetFilePath($categoryName, $id)
    {
        $filePaths = self::GetFilePaths($categoryName, $id);
        if (count($filePaths) === 0) {
            return null;
        }

        return $filePaths[0];
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

    static public function GetFileUri($categoryName, $id)
    {
        $fileUris = self::GetFileUris($categoryName, $id);
        if (count($fileUris) === 0)
            return null;

        return $fileUris[0];
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

    static public function Scale($file, $fileMediaPath, $size)
    {
        $filePath = E\Path::Media('FilesUpload', $fileMediaPath);
        if (!file_exists(dirname($filePath)))
            mkdir(dirname($filePath), 0777, true);

        return EC\HImages::Scale_ToMinSize($file['tmp_name'],
                $filePath, $size[0], $size[1]);
    }

    static public function Upload(string $categoryName, string $id, $file)
    {
        $categories = EC\HConfig::GetRequired('FilesUpload', 'categories');

        if (!array_key_exists($categoryName, $categories))
            throw new \Exception("Upload category '{$categoryName}' does not exist.");
        $category = $categories[$categoryName];

        if ($file['tmp_name'] === '') {
            throw new \Exception("Cannot upload file.");
        }

        $fileName_Parsed = $file['name'];
        $fileName_Parsed = mb_strtolower($fileName_Parsed);
        $fileName_Parsed = EC\HStrings::EscapeLangCharacters($fileName_Parsed);
        $fileName_Parsed = str_replace(' ', '-', $fileName_Parsed);
        $fileName_Parsed = EC\HStrings::RemoveCharacters($fileName_Parsed, 
                'qwertyuiopasdfghjklzxcvbnm' . 
                '._-');
        $fileName_Parsed = EC\HStrings::RemoveDoubles($fileName_Parsed, ' ');

        $fileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $fileExt = mb_strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileDir = $category['alias'];
        $fileMediaPath = $category['multiple'] ? 
                "{$fileDir}-{$id}/{$fileName_Parsed}.{$fileExt}" :
                "{$fileDir}-{$id}.{$fileExt}";

        if ($category['type'] === 'image') {
            foreach ($category['sizes'] as $sizeName => $size) {
                $tSizeName = $sizeName === '$default' ? '' : "_{$sizeName}";

                if ($category['multiple'])
                    $tFileName = "-{$id}{$tSizeName}/{$fileName_Parsed}";
                else
                    $tFileName = "-{$id}{$tSizeName}";

                if ($size === null)
                    self::Copy($file, "{$fileDir}{$tFileName}.{$fileExt}");
                else {
                    if (!self::Scale($file, $fileMediaPath, $size))
                        throw new \Exception('Cannot scale image.');
                }
            }
        } else if ($category['type'] === 'file') {
            if (!self::Copy($file, $fileMediaPath))
                throw new \Exception('Cannot copy image.');
        } else
            throw new \Exception("Unknown category type '{$category['type']}.");

        return true;
    }

}