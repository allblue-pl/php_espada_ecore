<?php namespace EC\FilesUpload;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Api\CArgs;
use EC\Api\CResult;
use EC\Api\SApi;
use EC\Api\SUserApi;
use EC\Config\HConfig;
use EC\Users\MUser;

class AFilesUpload extends EC\Api\AUserApi {
    private $categories;


    public function __construct(SUserApi $site, array $apiArgs) {
        parent::__construct($site, $apiArgs['requiredPermissions']);

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

        $this->categories = HConfig::GetRequired('FilesUpload', 'categories');
    }

    public function action_Delete(CArgs $args) {
        try {
            HFilesUpload::DeleteFile($args->get("categoryName"), $args->get("id"), 
                    $args->get("fileName"));
        } catch (\Exception $e) {
            /** @phpstan-ignore if.alwaysTrue */
            if (EDEBUG)
                throw $e;
                
            /** @phpstan-ignore deadCode.unreachable */
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

    public function action_List(CArgs $args) {
        if (!array_key_exists($args->get("categoryName"), $this->categories))
            return CResult::Failure("Upload category '{$args->get("categoryName")}' does not exist.");

        $files = HFilesUpload::GetFileInfos($args->get("categoryName"), 
                $args->get("id"));

        return CResult::Success()
            ->add('files', $files);
    }

    public function action_Upload(CArgs $args) {
        try {
            HFilesUpload::Upload($args->get("categoryName"), $args->get("id"), 
                    $args->get("file"));
        } catch (\Exception $e) {
            /** @phpstan-ignore if.alwaysTrue */
            if (EDEBUG)
                throw $e;
            
            /** @phpstan-ignore deadCode.unreachable */
            return CResult::Failure($e->getMessage());
        }

        $category = HFilesUpload::GetCategory($args->get("categoryName"));
        $fileInfo = $category['multiple'] ?
                HFilesUpload::GetFileInfo_Multiple($args->get("categoryName"), 
                    $args->get("id"), $args->get("file['name']")) :
                HFilesUpload::GetFileInfo_Single($args->get("categoryName"), 
                        $args->get("id"));

        return CResult::Success()
            ->add('fileInfo', $fileInfo);
    }

}
