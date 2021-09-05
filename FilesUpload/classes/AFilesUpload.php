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
        // $this->action('fix', 'action_Fix', [

        // ]);
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
        try {
            HFilesUpload::DeleteFile($args->categoryName, $args->id, 
                    $args->fileName);
        } catch (\Exception $e) {
            if (EDEBUG)
                throw $e;
                
            return CResult::Failure($e->getMessage());
        }

        return CResult::Success();
    }

    // public function action_Fix()
    // {
    //     $dirPath = E\Path::Media('FilesUpload', 'articles');
    //     $files = scandir($dirPath);
    //     foreach ($files as $file) {
    //         if (mb_strpos($file, 'intro-') !== 0)
    //             continue;

    //         $filePath = "{$dirPath}/$file";
    //         $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            
    //         mkdir("{$dirPath}/{$fileName}");
    //         HFilesUpload::Scale($filePath, "{$dirPath}/{$fileName}/{$fileName}.jpg",
    //             [ 960, 640 ]);
    //         HFilesUpload::Scale($filePath, "{$dirPath}/{$fileName}/{$fileName}_thumbnail.jpg",
    //             [ 320, 280 ]);
    //         unlink($filePath);
    //     }
    // }

    public function action_List(CArgs $args)
    {
        if (!array_key_exists($args->categoryName, $this->categories))
            return CResult::Failure("Upload category '{$args->categoryName}' does not exist.");

        $files = HFilesUpload::GetFileInfos($args->categoryName, $args->id);

        return CResult::Success()
            ->add('files', $files);
    }

    public function action_Upload(CArgs $args)
    {
        try {
            HFilesUpload::Upload($args->categoryName, $args->id, $args->file);
        } catch (\Exception $e) {
            if (EDEBUG)
                throw $e;
                
            return CResult::Failure($e->getMessage());
        }

        $category = HFilesUpload::GetCategory($args->categoryName);
        $fileInfo = $category['multiple'] ?
                HFilesUpload::GetFileInfo_Multiple($args->categoryName, 
                    $args->id, $args->file['name']) :
                HFilesUpload::GetFileInfo_Single($args->categoryName, $args->id);

        return CResult::Success()
            ->add('fileInfo', $fileInfo);
    }

}
