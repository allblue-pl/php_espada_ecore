<?php namespace EC\FilesUpload;
defined('_ESPADA') or die(NO_ACCESS);

use Closure;
use E, EC;
use EC\Api\CArgs;
use EC\Api\CResult;
use EC\Api\SApi;
use EC\Api\SUserApi;
use EC\Config\HConfig;
use EC\Users\MUser;

/**
 * @phpstan-type _T_CategoryPermissionFn Closure(string, "r"|"w"): bool
 */
class AFilesUpload extends EC\Api\AUser {
    /** @var list<string> $categories */
    private array $categories;
    /** @var array<string,_T_CategoryPermissionFn> $categoryPermissions */
    private array $categoryPermissions;


    public function __construct(SUserApi $site, array $apiArgs) {
        parent::__construct($site, $apiArgs['requiredPermissions']);

        $this->categories = HConfig::GetRequired('FilesUpload', 'categories');
        $this->categoryPermissions = [];

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
    }

    /**
     * @param _T_CategoryPermissionFn $permissionValidator 
     */
    public function addCategoryPermission(string $categoryName, 
            Closure $permissionValidator) {
        $this->categoryPermissions[$categoryName] = $permissionValidator;
    }

    public function action_Delete(CArgs $args) {
        $categoryName = strval($args->get("categoryName"));
        $id = strval($args->get("id"));
        $fileName = strval($args->get("fileName"));

        if (array_key_exists($categoryName, $this->categoryPermissions)) {
            if (!$this->categoryPermissions[$categoryName]($id, "w"))
                return CResult::Failure("Permission denied.");
        }

        try {
            HFilesUpload::DeleteFile($categoryName, $id, $fileName);
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

    public function action_List(CArgs $args) {
        $categoryName = strval($args->get("categoryName"));
        $id = strval($args->get("id"));

        if (!array_key_exists($categoryName, $this->categories))
            return CResult::Failure("Upload category '{$args->get("categoryName")}' does not exist.");

        if (array_key_exists($categoryName, $this->categoryPermissions)) {
            if (!$this->categoryPermissions[$categoryName]($id, "r"))
                return CResult::Failure("Permission denied.");
        }

        $files = HFilesUpload::GetFileInfos($categoryName, $id);

        return CResult::Success()
            ->add('files', $files);
    }

    public function action_Upload(CArgs $args) {
        $categoryName = strval($args->get("categoryName"));
        $id = strval($args->get("id"));

        if (array_key_exists($categoryName, $this->categoryPermissions)) {
            if (!$this->categoryPermissions[$categoryName]($id, "w"))
                return CResult::Failure("Permission denied.");
        }

        try {
            HFilesUpload::Upload($categoryName, $id, $args->get("file"));
        } catch (\Exception $e) {
            if (EDEBUG)
                throw $e;
            
            return CResult::Failure($e->getMessage());
        }

        $category = HFilesUpload::GetCategory($args->get("categoryName"));
        $fileInfo = $category['multiple'] ?
                HFilesUpload::GetFileInfo_Multiple($args->get("categoryName"), 
                    $args->get("id"), $args->get("file")["name"]) :
                HFilesUpload::GetFileInfo_Single($args->get("categoryName"), 
                        $args->get("id"));

        return CResult::Success()
            ->add('fileInfo', $fileInfo);
    }
}
