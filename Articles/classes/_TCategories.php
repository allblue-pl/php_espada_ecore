<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Database\TTable;

class _TCategories extends TTable {

    public function __construct(MDatabase $db, $tablePrefix) {
        parent::__construct($db, 'Articles_Categories', $tablePrefix);

        $this->setColumns([
            'Id' => new Database\FInt(true),
            'Name' => new Database\FString(true, 16),
            'Title' => new Database\FString(true, 64),
        ]);
    }

}
