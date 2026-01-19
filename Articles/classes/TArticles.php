<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database;
use EC\Database\MDatabase;
use EC\Date\HDate;
use EC\Strings\HStrings;

class TArticles extends _TArticles {

    static public function GetNew(MDatabase $db, $userId) {
        $table = new TArticles($db);

        $row = $table->row_Where([
            [ 'User_Id', '=', $userId ],
            [ 'User_New', '=', true ],
        ]);

        if ($row !== null)
            return $row;

        $table->update([[
            'Id' => null,
            'User_Id' => $userId,
            'User_New' => true,
            'Publish' => time(),
            'Published' => true,
            'AuthorName' => '',
            'Title' => '',
            'Intro' => '',
            'IntroImage_Description' => '',
            'Content_Raw' => '',
            'Content_Html' => '',
        ]]);

        return $table->row_ById($table->getLastInsertedId());
    }

    static public function GetWhereConditions_Published() {
        $whereConditions = [
            [ 'Publish', '<=', time() ],
            [ 'Published', '=', true ],
        ];

        return $whereConditions;
    }


    public function __construct(MDatabase $db) {
        parent::__construct($db, 'a_a');

        $time = $db->escapeTime_DateTime(HDate::GetTime());

        /* Columns */
        $this->addColumns_Extra([
            'Alias'             => [ null, new EC\Database\FString(false, 128) ],
            'IntroImage_Uri'     => [ null, new Database\FString(false, 256) ],
            'IsPublished'       => [ "a_a.Published AND a_a.Publish <= $time", 
                    new Database\FBool(false) ],
        ]);

        HArticles::AddColumnParsers($this);

        /* Validators */
        $this->setColumnVFields('Title', [
            'required' => true,
            'chars' => HStrings::GetCharsRegexp_Basic('\r\n') . '"',
        ]);
        $this->setColumnVFields('Intro', [
            'required' => false,
            'chars' => null,
        ]);
        $this->setColumnVFields('IntroImage_Description', [
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
        $this->setColumnVFields('AuthorName', [
            'required' => false,
        ]);
    }

}
