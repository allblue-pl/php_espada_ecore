<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TArticles extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, 'Articles_Articles', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true),
            'User_Id' => new Database\FInt(true),
            'User_New' => new Database\FBool(true),
            'Publish' => new Database\FDateTime(true),
            'Published' => new Database\FBool(true),
            'AuthorName' => new Database\FVarchar(true, 128),
            'Title' => new Database\FVarchar(true, 128),
            'Intro' => new Database\FVarchar(true, 1024),
            'Content_Raw' => new Database\FText(true, 'medium'),
            'Content_Html' => new Database\FText(true, 'medium'),
            'Gallery' => new Database\FBool(true),
        ]);
    }

}
