<?php namespace EC\FilesUpload;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Api\CArgs, EC\Api\CResult;

class AFilesUpload extends EC\Api\ABasic
{

    private $config = null;


    public function __construct(EC\SApi $site, array $apiArgs)
    {
        parent::__construct($site, $apiArgs['userType'], $apiArgs['requiredPermissions']);

        $this->user = $site->m->user;

        $this->action('delete', 'action_Delete', [
            'categoryName' => true,
            'id' => true,
            'fileName' => true,
        ]);
        $this->action('list', 'action_List', [
            'categoryName' => true,
            'id' => true,
        ]);
        $this->action('upload', 'action_Upload', [
            'categoryName' => true,
            'id' => true,
            'fileName' => true,

            'file' => true,
        ]);

        $this->categories = EC\HConfig::GetRequired('FilesUpload', 'categories');
    }

    public function action_Delete(CArgs $args)
    {
        if (!EC\HFilesUpload::ExistsCategory($args->categoryName))
            return CResult::Failure("Upload category '{$args->categoryName}' does not exist.");
        $category = EC\HFilesUpload::GetCategory($args->categoryName);

        $dirPath = HFilesUpload::GetDirMediaPath($args->categoryName, $args->id);
        $files = HFilesUpload::GetFilePaths($args->categoryName, $args->id);

        $fileToDelete = null;
        if ($category['multiple']) {
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_BASENAME) === $args->fileName) {
                    $fileToDelete = $file;
                    break;
                }
            }
        } else
            $fileToDelete = $files[0];

        if ($fileToDelete === null)
            return CResult::Failure('File does not exist.' . $args->fileName);

        if (!unlink($fileToDelete))
            return CResult::Failure('Cannot delete file.');

        return CResult::Success();
    }

    public function action_List(CArgs $args)
    {
        if (!array_key_exists($args->categoryName, $this->categories))
            return CResult::Failure("Upload category '{$args->categoryName}' does not exist.");

        $files = HFilesUpload::GetFileUris($args->categoryName, $args->id);

        return CResult::Success()
            ->add('files', $files);
    }

    public function action_Upload(CArgs $args)
    {
        if ($args->_debug && $args->_test) {
            $args->file = [
                'name' => 'hello-test.jpg',
                'tmp_name' => PATH_ESITE . '/tests/test.jpg',
            ];
        }

        if (!array_key_exists($args->categoryName, $this->categories))
            return CResult::Failure("Upload category '{$args->categoryName}' does not exist.");
        $category = $this->categories[$args->categoryName];

        if (!$this->user->hasPermissions($category['permissions'])) {
            return CResult::Failure('Permission denied.')
                ->debug('Required permissions: ' . implode(', ', $category['permissions']));
        }

        if ($args->file['tmp_name'] === '') {
            return CResult::Failure(EC\HText::_('FilesUpload:error_CannotUploadFile'));
        }

        if (!(EC\HStrings::ValidateChars($args->fileName, 'a-z0-9._-', 
                $invalidChars))) {
            return CResult::Failure(EC\HText::_('FilesUpload:errors_InvalidCharsInFileName', 
                    [ implode(', ', $invalidChars) ]));
        }

        $fileName = pathinfo($args->fileName, PATHINFO_FILENAME);
        $fileExt = mb_strtolower(pathinfo($args->file['name'], PATHINFO_EXTENSION));
        $fileDir = $category['alias'];
        $fileMediaPath = $category['multiple'] ? 
                "{$fileDir}-{$args->id}/{$fileName}.{$fileExt}" :
                "{$fileDir}-{$args->id}.{$fileExt}";

        if ($category['type'] === 'image') {
            foreach ($category['sizes'] as $sizeName => $size) {
                $tSizeName = $sizeName === '$default' ? '' : "_{$sizeName}";

                if ($category['multiple'])
                    $tFileName = "-{$args->id}{$tSizeName}/{$fileName}";
                else
                    $tFileName = "-{$args->id}{$tSizeName}";

                if ($size === null)
                    $this->copy($args->file, "{$fileDir}{$tFileName}.{$fileExt}");
                else {
                    if (!$this->scale($args->file, $fileMediaPath, $size))
                        return CResult::Failure('Cannot scale image.');
                }
            }
        } else if ($category['type'] === 'file') {
            if (!$this->copy($args->file, $fileMediaPath))
                return CResult::Failure('Cannot copy file.');
        } else
            throw new \Exception("Unknown category type '{$category['type']}.");

        return CResult::Success()
            ->add('uri', E\Uri::Media('FilesUpload', $fileMediaPath));
    }


    private function copy($file, $fileMediaPath)
    {
        $filePath = E\Path::Media('FilesUpload', $fileMediaPath);
        if (!file_exists(dirname($filePath)))
            mkdir(dirname($filePath), 0777, true);

        return copy($file['tmp_name'], $filePath);
    }

    private function scale($file, $fileMediaPath, $size)
    {
        $filePath = E\Path::Media('FilesUpload', $fileMediaPath);
        if (!file_exists(dirname($filePath)))
            mkdir(dirname($filePath), 0777, true);

        return EC\HImages::Scale_ToMinSize($file['tmp_name'],
                $filePath, $size[0], $size[1]);
    }

}
