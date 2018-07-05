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


    public function __construct(EC\MDatabase $db, string $categoriesTableName,
           string $categoriesTableAlias, array $categoryNames)
    {
        parent::__construct($db, 'a_a');

        $time = $db->escapeTime_DateTime(EC\HDate::Time());

        /* Categories */
        $categoryColumns = [];
        foreach ($categoryNames as $categoryName) {
            $categoryColumns["Category_{$categoryName}"] = [ 
                    "{$categoriesTableAlias}.{$categoryName}",
                    new Database\FVarchar(true, 16) ];
        }
        $this->addColumns_Extra($categoryColumns);

        /* Columns */
        $this->addColumns_Extra([
            'IsPublished' => [ "a_a.Published AND a_a.Publish <= $time", 
                    new Database\FBool(false) ],
        ]);

        $this->setJoin(
            " INNER JOIN {$categoriesTableName} AS {$categoriesTableAlias}" .
            " ON {$categoriesTableAlias}.Article_Id = a_a.Id"
        );

        /* Validators */
        $this->setColumnVFields('Content_Raw', [
            'required' => false,
            'chars' => null,
        ]);
        $this->setColumnVFields('Content_Html', [
            'required' => false,
            'chars' => null,
        ]);

        /* Parsers */
        $this->setColumnParser('Id', [
            'out' => function($row, $name, $value) {
                $colNames = [
                    'Title' => str_replace('Id', 'Title', $name),
                    'Alias' => str_replace('Id', 'Alias', $name),
                    'Uri' => str_replace('Id', 'Uri', $name),
                    'ImageUri' => str_replace('Id', 'ImageUri', $name),
                ];

                $alias = EC\HWeb::GetAlias($value, $row[$colNames['Title']]);

                $uri = '/';
                if (((int)$row['Category']) === EC\HWeb::ArticleCategories_News) {
                    $uri = E\Uri::Page('news.article', [
                        'article' => $alias
                    ]);
                }

                $imageUri = E\Uri::Media('Web', 'articles/' . $value . '-image.jpg');
                if ($imageUri === null)
                    $imageUri = Web\HLeagueType::FileUri('images/article-image.jpg');

                return [
                    $name => $value,
                    $colNames['Alias']     => $alias,
                    $colNames['Uri']  => $uri,
                    $colNames['ImageUri']  => $imageUri
                ];
            }
        ]);
    }

}
