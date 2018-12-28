<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HArticles
{

    static public function Alias_Format($str)
    {
        $str = trim(mb_strtolower($str));
        $str = EC\HStrings::EscapeLangCharacters($str);
        $str = str_replace(' ', '-', $str);
        $str = EC\HStrings::RemoveCharacters($str,
                'qwertyuiopasdfghjklzxcvbnm0123456789-');
        $str = EC\HStrings::RemoveDoubles($str, '-');

        return $str;
    }

    static public function Alias_Get($id, $title)
    {
        return intval($id) . '-' . self::Alias_Format($title);
    }

    static function Config(EC\Config\CConfig_Setter $eConfig)
    {
        $eConfig->set([
            'FilesUpload' => [
                'categories' => [
                    'eArticles_Intro' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'multiple' => false,
                        'alias' => 'articles/intro',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                        ],
                    ],

                    'eArticles_Files' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'file',
                        'multiple' => true,
                        'alias' => 'articles/files',
                    ],

                    'eArticles_Images' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'multiple' => true,
                        'alias' => 'articles/images',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                        ],
                    ],

                    'eArticles_Gallery' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'multiple' => true,
                        'alias' => 'articles/gallery',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    static public function DeleteMedia($articleId)
    {
        EC\HFilesUpload::DeleteFiles('eArticles_Intro', $articleId);
        EC\HFilesUpload::DeleteFiles('eArticles_Files', $articleId);
        EC\HFilesUpload::DeleteFiles('eArticles_Images', $articleId);
        EC\HFilesUpload::DeleteFiles('eArticles_Gallery', $articleId);
    }

    static public function GetNew(EC\MDatabase $db, $userId)
    {
        $table = new TArticles($db);

        $row = $table->row_Where([
            [ 'User_Id', '=', $userId ],
            [ 'User_New', '=', true ],
        ]);

        if ($row !== null)
            return $row;

        $table->update([[
            'User_Id' => $userId,
            'User_New' => true,
        ]]);

        return $table->row_ById($db->getInsertedId());
    }

    static public function Init(EC\MELibs $eLibs, $pkgsUri)
    {
        $eLibs->addTranslations('Articles');
        $eLibs->setField('eArticles', [
            'spkTinyMCEPkgUri' => $pkgsUri . '/node_modules/spk-tinymce',
        ]);
    }

}
