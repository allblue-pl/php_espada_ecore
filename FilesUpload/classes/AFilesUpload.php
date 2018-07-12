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

        if (!(EC\HStrings::ValidateChars($args->fileName, 'a-z0-9._-', 
                $invalidChars))) {
            return CResult::Failure(EC\HText::_('FilesUpload:errors_InvalidCharsInFileName', 
                    [ implode(', ', $invalidChars) ]));
        }

        $fileName = pathinfo($args->fileName, PATHINFO_FILENAME);
        $fileExt = pathinfo($args->file['name'], PATHINFO_EXTENSION);
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
                    
                    $this->copy($args->file, "${fileDir}{$tFileName}.{$fileExt}");
                }
            }
        } else if ($category['type'] === 'file') {
            // $this->copy($args->file['tmp_name'], $filePath);
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

        copy($file['tmp_name'], $filePath);
    }

}
