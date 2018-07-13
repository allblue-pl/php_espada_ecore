<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database,
    EC\Web;

class TArticles extends _TArticles
{

    static public function WhereConditions_Categories(array $categoryNames)
    {
        $whereConditions = [];
        foreach ($categoryNames as $categoryName)
            $whereConditions[] = [ "Category_{$categoryName}", '=', true ];

        return [ 'OR' => $whereConditions ];
    }


    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'a_a');

        $time = $db->escapeTime_DateTime(EC\HDate::Time());

        /* Columns */
        $this->addColumns_Extra([
            'IsPublished' => [ "a_a.Published AND a_a.Publish <= $time", 
                    new Database\FBool(false) ],
        ]);

        /* Validators */
        $this->setColumnVFields('Intro', [
            'required' => false,
            'chars' => null,
        ]);
        $this->setColumnVFields('Content_Raw', [
            'required' => false,
            'chars' => null,
        ]);
        $this->setColumnVFields('Content_Html', [
            'required' => false,
            'chars' => null,
        ]);
    }

}
