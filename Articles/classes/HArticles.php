<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HArticles
{

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
                            'full' => null,
                        ],
                    ],

                    'eArticles_Gallery' => [
                        'permissions' => [ 'Articles_Articles' ],
                        'type' => 'image',
                        'multiple' => true,
                        'alias' => 'articles/gallery',
                        'sizes' => [
                            '$default' => [ 1920, 1080 ],
                            'full' => null,
                        ],
                    ],
                ],
            ],
        ]);
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
        $eLibs->setField('eArticles', [
            'spkTinyMCEPkgUri' => $pkgsUri . '/node_modules/spk-tinymce',
        ]);
    }

}