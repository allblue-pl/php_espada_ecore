<?php namespace EC\Articles;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database,
    EC\Web;

class TCategories extends _TCategories {

    public function __construct(EC\MDatabase $db) {
        parent::__construct($db, 'a_c');
    }

}
