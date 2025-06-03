<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CUrl {

    public $loc = null;
    public $lastMod = null;
    public $changeFreq = null;

    public function __construct($loc) {
        $this->loc = $loc;
    }

}