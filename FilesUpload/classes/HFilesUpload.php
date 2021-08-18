<?php namespace EC\FilesUpload;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HFilesUpload
{

    static public function Copy($filePath_Src, $filePath)
    {
        EC\HFiles::Dir_Create_Safe(dirname($filePath));

        return copy($filePath_Src, $filePath);
    }

    static public function Delete($categoryName, $id)
    {
        $category = self::GetCategory($categoryName);

        if ($category['multiple']) {
            return self::DeleteFile_Multiple($categoryName, $id, null);
        } else {
            return self::DeleteFile_Single($categoryName, $id);
        }
    }

    static public function DeleteFile($categoryName, $id, $fileName = null)
    {
        $category = EC\HFilesUpload::GetCategory($categoryName);  
        if ($category['multiple'])
            return self::DeleteFile_Multiple($categoryName, $id, [ $fileName ]);
        else
            return self::DeleteFile_Single($categoryName, $id);
    }

    static public function DeleteFile_Single($categoryName, $id)
    {
        $category = EC\HFilesUpload::GetCategory($categoryName);  

        foreach ($category['sizes'] as $sizeName => $size) {
            $filePath = HFilesUpload::GetFilePath($categoryName, $id, $sizeName);
            if ($filePath !== null) {
                if (!unlink($filePath))
                    throw new \Exception("Cannot delete file '{$filePath}'.");
            }
        }

        $dirPath = self::GetDirPath($categoryName, $id);
        if (file_exists($dirPath)) {
            if (is_dir($dirPath))
                return rmdir($dirPath);
        }

        return true;
    }

    static public function DeleteFile_Multiple($categoryName, $id, 
            ?array $fileNames = null)
    {
        $category = self::GetCategory($categoryName);

        foreach ($category['sizes'] as $sizeName => $size) {
            $filePaths = HFilesUpload::GetFilePaths($categoryName, $id, $sizeName);
            $filePathToDelete = null;

            if ($fileNames === null) {
                foreach ($filePaths as $filePath) {
                    if (!unlink($filePath))
                        throw new \Exception("Cannot delete file '{$filePathToDelete}'.");
                }
            } else {
                foreach ($fileNames as $fileName) {
                    $deleted = false;
                    foreach ($filePaths as $filePath) {
                        $fileName_Raw = pathinfo($filePath, PATHINFO_BASENAME);
                        if ($fileName_Raw === $fileName) {
                            if (!unlink($filePath))
                                throw new \Exception("Cannot delete file '{$filePathToDelete}'.");
                            $deleted = true;
                            break;
                        }
                    }
    
                    if (!$deleted)
                        throw new \Exception("File '{$fileName}' does not exist.");
                }
            }            

            $deleteDir = false;
            if ($fileNames === null)
                $deleteDir = true;
            else if (count($filePaths) === count($fileNames))
                $deleteDir = true;

            if ($deleteDir) {
                $size_Postfix = $sizeName === '$default' ? '' : "_{$sizeName}";
                $dirPath = self::GetDirPath($categoryName, $id) . $size_Postfix;
                if (file_exists($dirPath)) {
                    if (is_dir($dirPath)) {
                        if (!rmdir($dirPath))
                            throw new \Exception("Cannot delete dir '{$dirPath}'.");
                    }
                }
            }
        }

        return true;
    }

    static public function GetCategory(string $categoryName)
    {
        $categories = EC\HConfig::GetRequired('FilesUpload', 'categories');
        if (!array_key_exists($categoryName, $categories))
            throw new \Exception("FilesUpload category '{$categoryName}' does not exist.");

        $category = array_replace_recursive([
            'permissions' => [],
            'type' => 'file',
            'exts' => null,
            'compress' => false,
            'multiple' => false,
            'alias' => 'file',
            'sizes' => [ '$default' => null, ],
        ], $categories[$categoryName]);

        if ($category['type'] === 'file') {
            $category['compress'] = false;
            $category['sizes'] = [ '$default' => null, ];
        }

        return $category;
    }

    static public function GetDirMediaPath(string $categoryName, $id)
    {
        $category = self::GetCategory($categoryName);

        $dirMediaPath = "{$category['alias']}-{$id}";

        return $dirMediaPath;
    }

    static public function GetDirPath($categoryName, $id)
    {
        return E\Path::Media('FilesUpload', self::GetDirMediaPath($categoryName, $id));
    }

    static public function GetDirUri($categoryName, $id)
    {
        return E\Uri::Media('FilesUpload', self::GetDirMediaPath($categoryName, $id));
    }

    static public function GetFileMediaPath_Multiple($categoryName, $id, 
            $fileFullName, $sizeName = '$default')
    {
        $fileName = pathinfo($fileFullName, PATHINFO_FILENAME);
        $fileName_Parsed = self::ParseFileName($fileName);
        $fileExt = mb_strtolower(pathinfo($fileFullName, PATHINFO_EXTENSION));

        $category = self::GetCategory($categoryName);
        $dirMediaPath = self::GetDirMediaPath($categoryName, $id);
    
        $sizeName_Postfix = '';
        if ($sizeName !== '$default') {
            $sizeName_Postfix = "_{$sizeName}";
        }

        if ($category['type'] === 'image' && $category['compress']) {
            $fileExt = 'jpg';
    }

        return "{$dirMediaPath}{$sizeName_Postfix}/{$fileName_Parsed}.{$fileExt}";
    }

    static public function GetFileMediaPath_Single($categoryName, $id, $ext,
            $sizeName = '$default')
    {
        $category = self::GetCategory($categoryName);
        $dirMediaPath = self::GetDirMediaPath($categoryName, $id);
        $aliasArr = explode('/', $category['alias']);
        $fileName = $aliasArr[count($aliasArr) -1];
        $sizeName_Postfix = '';
        if ($sizeName !== '$default') {
            $sizeName_Postfix = "_{$sizeName}";
        }

        if ($category['type'] === 'image' && $category['compress']) {
            $ext = 'jpg';
        }

        return "{$dirMediaPath}/{$fileName}-{$id}{$sizeName_Postfix}.{$ext}";
    }

    static public function GetFileMediaPaths($categoryName, $id, 
            $sizeName = '$default')
    {
        $category = self::GetCategory($categoryName);
        $dirMediaPath = self::GetDirMediaPath($categoryName, $id);

        if ($category['multiple']) {
            $size_Postfix = '';
            if ($sizeName !== '$default')
                $size_Postfix = "_{$sizeName}";

            $dirPath = E\Path::Media('FilesUpload', "{$dirMediaPath}{$size_Postfix}"); 

            if (!file_exists($dirPath))
                return [];
            if (!is_dir($dirPath))
                return [];

            $files = array_filter(array_diff(scandir($dirPath), [ '.', '..' ]), 
                    function($file) use ($category, $id, $dirPath) {
                if (is_dir("{$dirPath}/$file")) {
                        return false;
                }

                return true;
            });

            $fileMediaPaths = [];
            foreach ($files as $file) {
                $fileMediaPaths[] = "{$dirMediaPath}{$size_Postfix}/$file";
            }

            return $fileMediaPaths;
        } else {
            $exts = $category['exts'];
            if ($category['type'] === 'image' && $category['compress'])
                $exts = [ 'jpg' ];

            foreach ($exts as $ext) {
                $mediaPath = self::GetFileMediaPath_Single($categoryName, $id,
                        $ext, $sizeName);
                if (file_exists(E\Path::Media('FilesUpload', $mediaPath))) {
                    return [
                        $mediaPath,
                    ];
                }
            }

            return [];
        }
    }

    static public function GetFilePath($categoryName, $id, $sizeName = '$default')
    {
        $filePaths = self::GetFilePaths($categoryName, $id, $sizeName);
        if (count($filePaths) === 0) {
            return null;
        }

        return $filePaths[0];
    }

    static public function GetFilePaths($categoryName, $id, $sizeName = '$default')
    {
        $fileMediaPaths = self::GetFileMediaPaths($categoryName, $id, $sizeName);

        $filePaths = [];
        foreach ($fileMediaPaths as $fileMediaPath)
            $filePaths[] = E\Path::Media('FilesUpload', $fileMediaPath);

        return $filePaths;
    }

    static public function GetFileUri_Single($categoryName, $id, $sizeName = '$default')
    {
        $category = self::GetCategory($categoryName);
        if ($category['multiple'])
            throw new \Exception('Wrong category type.');

        $fileUris = self::GetFileUris($categoryName, $id, $sizeName);
        if (count($fileUris) === 0)
            return null;

        return $fileUris[0];
    }

    static public function GetFileUri_Multiple($categoryName, $id, $fileName)
    {
        $category = self::GetCategory($categoryName);
        if (!$category['multiple'])
            throw new \Exception('Wrong category type.');

        $fileName_Parsed = self::ParseFileName($fileName);
        if ($category['type'] === 'image' && $category['compress'] === true)
            $fileName_Parsed = pathinfo($fileName_Parsed, PATHINFO_FILENAME) . ".jpg";

        $fileUris = HFilesUpload::GetFileUris($categoryName, $id);
        foreach ($fileUris as $fileUri) {
            if (pathinfo($fileUri, PATHINFO_BASENAME) === $fileName_Parsed)
                return $fileUri;
        }

        return null;
    }

    static public function GetFileUris($categoryName, $id, $sizeName = '$default')
    {
        $fileMediaPaths = self::GetFileMediaPaths($categoryName, $id, $sizeName);

        $fileUris = [];
        foreach ($fileMediaPaths as $fileMediaPath)
            $fileUris[] = E\Uri::Media('FilesUpload', $fileMediaPath);

        return $fileUris;
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

    static public function ParseFileName($fileName)
    {
        $fileName_Parsed = $fileName;
        $fileName_Parsed = mb_strtolower($fileName_Parsed);
        $fileName_Parsed = EC\HStrings::EscapeLangCharacters($fileName_Parsed);
        $fileName_Parsed = str_replace(' ', '-', $fileName_Parsed);
        $fileName_Parsed = EC\HStrings::RemoveCharacters($fileName_Parsed, 
                'a-z0-9' . '\\._\\-');
        $fileName_Parsed = EC\HStrings::RemoveDoubles($fileName_Parsed, ' ');

        return $fileName_Parsed;
    }

    static public function Scale($filePath_Src, $filePath, $size)
    {
        EC\HFiles::Dir_Create_Safe(dirname($filePath), 0777, true);
        // if (!file_exists(dirname($filePath)))
        //     mkdir(dirname($filePath), 0777, true);

        return EC\HImages::Scale_ToMinSize($filePath_Src,
                $filePath, $size[0], $size[1]);
    }

    static public function Upload(string $categoryName, string $id, $file)
    {
        $categories = EC\HConfig::GetRequired('FilesUpload', 'categories');

        if (!array_key_exists($categoryName, $categories)) {
            throw new \Exception("Upload category '{$categoryName}' does not exist.");
        }
        $category = $categories[$categoryName];

        if ($file['tmp_name'] === '') {
            throw new \Exception("Cannot upload file.");
        }

        $fileExt = mb_strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($category['exts'] !== null) {
            if (!in_array($fileExt, $category['exts'])) {
                throw new \Exception("Wrong file extension '{$fileExt}'." .
                        " Allowed types: " . implode(',', $category['exts']));
            }
        }

        if ($category['type'] === 'image') {
            foreach ($category['sizes'] as $sizeName => $size) {
                $fileMediaPath = null;

                if ($category['multiple'])
                    $fileMediaPath = self::GetFileMediaPath_Multiple(
                            $categoryName,$id, $file['name'], $sizeName);
                else {
                    $fileMediaPath = self::GetFileMediaPath_Single($categoryName,
                            $id, $fileExt, $sizeName);
                }

                $filePath = E\Path::Media('FilesUpload', $fileMediaPath);

                if ($size === null) {
                    if (!self::Copy($file['tmp_name'], $filePath)) {
                        throw new \Exception('Cannot copy image.');
                    }
                } else {
                    if (!self::Scale($file['tmp_name'], $filePath, $size))
                        throw new \Exception('Cannot scale image.');
                }
            }
        } else if ($category['type'] === 'file') {
            $fileMediaPath = null;

            if ($category['multiple'])
                $fileMediaPath = self::GetFileMediaPath_Multiple(
                        $categoryName, $id, $file['name']);
            else {
                $fileMediaPath = self::GetFileMediaPath_Single($categoryName,
                        $id, $fileExt);
            }

            $filePath = E\Path::Media('FilesUpload', $fileMediaPath);

            if (!self::Copy($file['tmp_name'], $filePath))
                throw new \Exception('Cannot copy file.');
        } else
            throw new \Exception("Unknown category type '{$category['type']}.");

        return true;
    }

}