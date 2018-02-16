<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MOpenGraph extends E\Module
{

    private $mHeader = null;

    public function __construct(EC\Basic\MHeader $m_header)
    {
        $this->mHeader = $m_header;
    }

    public function addTag($name, $value)
    {
        $this->mHeader->addTag('meta', [
            'property' => $name,
            'content' => $value
        ], true);
    }

}
