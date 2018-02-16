<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database,
    EC\Web;

class TArticles extends Database\TTable
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'Articles_Articles', 'a_a');

        $time = $db->escapeTime_DateTime(EC\HDate::Time());

        $this->setColumns([
            'Id'        => new Database\FInt(true, 11),
            'User_Id'   => new Database\FInt(true, 11),

            'New'       => new Database\FBool(true, 11),

            'Category'      => new Database\FInt(true, 11),
            'Publish'       => new Database\FDateTime(true),
            'Published'     => new Database\FBool(true),

            'AuthorName'    => new Database\FVarchar(true, 128),

            'Title'         => new Database\FVarchar(true, 128),
            'Intro'         => new Database\FVarchar(true, 256),
            'Content_Raw'   => new Database\FText(true, 'medium'),
            'Content_Html'  => new Database\FText(true, 'medium'),

            'Gallery'       => new Database\FBool(true, 11),
        ]);
        // $this->addColumns_Extra([
        //     'IsPublished'       => [ "a.Published <= $time", new Database\FBool(false) ],

        //     'Alias'             => [ null, new Database\FVarchar(false, 256) ],
        //     'Uri'               => [ null, new Database\FVarchar(false, 256) ],
        //     'ImageUri'          => [ null, new Database\FVarchar(false, 256) ],
        // ]);

        /* Validators */
        $this->setColumnVFields('Content_Raw', [
            'required' => false,
            'chars' => null,
        ]);
        $this->setColumnVFields('Content_Html', [
            'required' => false,
            'chars' => null,
        ]);

    //     /* Parsers */
    //     $this->setColumnParser('Id', [
    //         'out' => function($row, $name, $value) {
    //             $col_names = [
    //                 'Title' => str_replace('Id', 'Title', $name),
    //                 'Alias' => str_replace('Id', 'Alias', $name),
    //                 'Uri' => str_replace('Id', 'Uri', $name),
    //                 'ImageUri' => str_replace('Id', 'ImageUri', $name),
    //             ];

    //             $alias = EC\HWeb::GetAlias($value, $row[$col_names['Title']]);

    //             $uri = '/';
    //             if (((int)$row['Category']) === EC\HWeb::ArticleCategories_News) {
    //                 $uri = E\Uri::Page('news.article', [
    //                     'article' => $alias
    //                 ]);
    //             }

    //             $image_uri = E\Uri::Media('Web', 'articles/' . $value . '-image.jpg');
    //             if ($image_uri === null)
    //                 $image_uri = Web\HLeagueType::FileUri('images/article-image.jpg');

    //             return [
    //                 $name => $value,
    //                 $col_names['Alias']     => $alias,
    //                 $col_names['Uri']  => $uri,
    //                 $col_names['ImageUri']  => $image_uri
    //             ];
    //         }
    //     ]);
    }

}
