<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database,
    EC\Web;

class TArticles extends _TArticles
{

    static public function AddColumns_Langs(EC\Database\TTable $table, array $langs)
    {
        $tableAlias = $table->getAlias();

        $colExpr = '';
        foreach ($langs as $lang) {
            if ($colExpr !== '')
                $colExpr .= ', ';
            $colExpr .= "IF({$tableAlias}.Lang_{$lang}, '$lang', NULL)";

            $table->setColumnVFields("Lang_{$lang}", [
                'required' => false,
            ]);
        }

        // foreach ($langs as $lang)
        //     $colExpr .= ')';

        $colExpr = "CONCAT_WS(', '," . $colExpr . ')';

        $table->addColumns_Extra([ 
            'Langs' => [ $colExpr, new EC\Database\FVarchar(true, 2) ],
        ]);
    }

    static public function FormatRow_Langs(array $langs, array &$row)
    {
        $lang_Row = $row['Lang'];
        unset($row['Lang']);

        foreach ($langs as $lang)
            $row["Lang_{$lang}"] = $lang === $lang_Row;
    }

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
            'Alias'         => [ null, new EC\Database\FVarchar(false, 128) ],
            'IntroImage'    => [ null, new Database\FVarchar(false, 256) ],
            'IsPublished'   => [ "a_a.Published AND a_a.Publish <= $time", 
                    new Database\FBool(false) ],
        ]);

        $this->addColumnParser('Id', [
            'out' => function($row, $name, $value) {
                $colNames = [
                    'Alias' => str_replace('Id', 'Alias', $name),
                    'IntroImage' => str_replace('Id', 'IntroImage', $name),
                    'Title' => str_replace('Id', 'Title', $name),
                ];

                $alias = EC\HArticles::Alias_Format($row[$colNames['Title']]);

                $introImages = EC\HFilesUpload::GetFileUris('eArticles_Intro', 
                        (int)$value);

                return [
                    $name => $value,
                    $colNames['Alias'] => $alias,
                    $colNames['IntroImage'] => count($introImages) === 0 ? 
                            null : $introImages[0],
                ];
            },
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
