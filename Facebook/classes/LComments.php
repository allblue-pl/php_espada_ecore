<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LComments extends E\Layout {

    public function __construct($href) {
        parent::__construct('Facebook:comments', [ 'href' => $href ]);
    }

}
