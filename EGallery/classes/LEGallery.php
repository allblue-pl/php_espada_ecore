<?php namespace EC\EGallery;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LEGallery extends E\Layout
{

    public function __construct()
    {
        parent::__construct('EGallery:eGallery', []);
    }

}
