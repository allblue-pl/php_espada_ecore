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
        //     'IntroImageUri'     => [ null, new EC\Database\FString(false, 256) ],
        //     'IsPublished'       => [ "a_a.Published AND a_a.Publish <= $time", 
        //             new EC\Database\FBool(false) ],
        // ]);

        $table->addColumnParser('Id', [
            'out' => function($row, $name, $value) {
                $colNames = [
                    'Alias' => str_replace('Id', 'Alias', $name),
                    'IntroImageUri' => str_replace('Id', 'IntroImageUri', $name),
                    'Title' => str_replace('Id', 'Title', $name),
                ];

                $alias = EC\HArticles::Alias_Format($row[$colNames['Title']]);

                $introImages = EC\HFilesUpload::GetFileUris('eArticles_Intro', 
                        (int)$value);

                return [
                    $name => $value,
                    $colNames['Alias'] => $alias,
                    $colNames['IntroImageUri'] => count($introImages) === 0 ? 
                            null : $introImages[0],
                ];
            },
        ]);
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
