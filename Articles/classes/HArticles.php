<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HArticles
{

    static public function AddColumnParsers(EC\Database\TTable $table)
    {
        $time = $table->getDB()->escapeTime_DateTime(EC\HDate::GetTime());

        // $table->addColumns_Extra([
        //     'Alias'             => [ null, new EC\Database\FString(false, 128) ],
        //     'IntroImage_Uri'     => [ null, new EC\Database\FString(false, 256) ],
        //     'IsPublished'       => [ "a_a.Published AND a_a.Publish <= $time", 
        //             new EC\Database\FBool(false) ],
        // ]);

        $table->addColumnParser('Id', [
            'out' => function($row, $name, $value) {
                $colNames = [
                    'Alias' => str_replace('Id', 'Alias', $name),
                    'IntroImage_Uri' => str_replace('Id', 'IntroImage_Uri', $name),
                    'Title' => str_replace('Id', 'Title', $name),
                ];

                $alias = EC\HArticles::Alias_Format($row[$colNames['Title']]);

                return [
                    $name => $value,
                    $colNames['Alias'] => $alias,
                    $colNames['IntroImage_Uri'] => EC\HFilesUpload::GetFileUri_Single(
                            'eArticles_Intro', (int)$value),
                ];
            },
        ]);

        // $table->addColumnParser('IntroImage_Description', [
        //     'out' => function($row, $name, $value) {
        //         $colNames = [
        //             'Title' => str_replace('IntroImage_Description', 'Title', $name),
        //         ];

        //         $alias = EC\HArticles::Alias_Format($row[$colNames['Title']]);

        //         return [
        //             $name => $row[$name] === '' ?
        //                     $row[$colNames['Title']] : 
        //                     $row[$name],
        //         ];
        //     },
        // ]);
    }

    static public function Alias_Format($str)
    {
        return EC\HRouter::GetAlias($str);
    }

    static public function Alias_Get($id, $title)
    {
        return intval($id) . '-' . self::Alias_Format($title);
    }

    static public function Alias_Parse($alias)
    {
        $regexp = "#^([0-9]+)-(.*)#";

        if (!preg_match($regexp, $alias, $match))
            return null;

        return [
            'id' => $match[1],
            'alias' => $match[2],
        ];
    }

    static function Config(EC\Config\CConfig_Setter $eConfig, array $overwrites = [])
    {
        $eConfig->set(array_replace_recursive([
            'FilesUpload' => [
                'categories' => [
                    'eArticles_Intro' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'exts' => [ 'jpg', 'jpeg', 'png', 'gif' ],
                        'compress' => true,
                        'multiple' => false,
                        'alias' => 'articles/intro',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                        ],
                    ],

                    'eArticles_Files' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'file',
                        'exts' => null,
                        'multiple' => true,
                        'alias' => 'articles/files',
                    ],

                    'eArticles_Images' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'exts' => [  'jpg', 'jpeg', 'png', 'gif' ],
                        'compress' => true,
                        'multiple' => true,
                        'alias' => 'articles/images',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                        ],
                    ],

                    'eArticles_Gallery' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'exts' => [  'jpg', 'jpeg', 'png', 'gif' ],
                        'compress' => true,
                        'multiple' => true,
                        'alias' => 'articles/gallery',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                        ],
                    ],
                ],
            ],
        ], $overwrites));
    }

    static public function DeleteMedia($articleId)
    {
        EC\HFilesUpload::Delete('eArticles_Intro', $articleId);
        EC\HFilesUpload::Delete('eArticles_Files', $articleId);
        EC\HFilesUpload::Delete('eArticles_Images', $articleId);
        EC\HFilesUpload::Delete('eArticles_Gallery', $articleId);
    }

    static public function GetMediaUris_Files($articleId)
    {
        return EC\HFilesUpload::GetFileUris('eArticles_Files', $articleId);
    }

    static public function GetMediaUris_Gallery($articleId)
    {
        return EC\HFilesUpload::GetFileUris('eArticles_Gallery', $articleId);
    }

    static public function GetMediaUris_Images($articleId)
    {
        return EC\HFilesUpload::GetFileUris('eArticles_Images', $articleId);
    }

    static public function GetMediaUris_Intro($articleId)
    {
        return EC\HFilesUpload::GetFileUris('eArticles_Intro', $articleId);
    }

    static public function GetNew(EC\MDatabase $db, $userId)
    {
        return TArticles::GetNew($db, $userId);
    }

    static public function Init(EC\MELibs $eLibs, $pkgsUri)
    {
        $eLibs->addTranslations('Articles');
        $eLibs->setField('eArticles', [
            'spkTinyMCEPkgUri' => $pkgsUri . '/node_modules/spk-tinymce',
        ]);
    }

}
