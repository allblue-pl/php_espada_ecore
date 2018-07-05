<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class _TCategories extends Database\TTable
{

    public function __construct(EC\MDatabase $db, $tablePrefix)
    {
        parent::__construct($db, 'Articles_Categories', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true),
            'Name' => new Database\FVarchar(true, 16),
            'Title' => new Database\FVarchar(true, 64),
        ]);
    }

}
