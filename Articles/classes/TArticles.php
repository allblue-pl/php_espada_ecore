<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database,
    EC\Web;

class TArticles extends _TArticles
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'a_a');

        $time = $db->escapeTime_DateTime(EC\HDate::Time());

        $this->addColumns_Extra([
            'IsPublished'       => [ "a_a.Published AND a_a.Publish <= $time", 
                    new Database\FBool(false) ],

            'Alias'             => [ null, new Database\FVarchar(false, 256) ],
            'Uri'               => [ null, new Database\FVarchar(false, 256) ],
            'ImageUri'          => [ null, new Database\FVarchar(false, 256) ],
        ]);

        /* Validators */
        $this->setColumnVFields('Content_Raw', [
            'required' => false,
            'chars' => null,
        ]);
        $this->setColumnVFields('Content_Html', [
            'required' => false,
            'chars' => null,
        ]);

        // /* Parsers */
        // $this->setColumnParser('Id', [
        //     'out' => function($row, $name, $value) {
        //         $colNames = [
        //             'Title' => str_replace('Id', 'Title', $name),
        //             'Alias' => str_replace('Id', 'Alias', $name),
        //             'Uri' => str_replace('Id', 'Uri', $name),
        //             'ImageUri' => str_replace('Id', 'ImageUri', $name),
        //         ];

        //         $alias = EC\HWeb::GetAlias($value, $row[$colNames['Title']]);

        //         $uri = '/';
        //         if (((int)$row['Category']) === EC\HWeb::ArticleCategories_News) {
        //             $uri = E\Uri::Page('news.article', [
        //                 'article' => $alias
        //             ]);
        //         }

        //         $imageUri = E\Uri::Media('Web', 'articles/' . $value . '-image.jpg');
        //         if ($imageUri === null)
        //             $imageUri = Web\HLeagueType::FileUri('images/article-image.jpg');

        //         return [
        //             $name => $value,
        //             $colNames['Alias']     => $alias,
        //             $colNames['Uri']  => $uri,
        //             $colNames['ImageUri']  => $imageUri
        //         ];
        //     }
        // ]);
    }

}
