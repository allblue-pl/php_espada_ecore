<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MOpenGraph extends E\Module
{

    private $header = null;

    public function __construct(EC\Basic\MHead $m_header)
    {
        $this->header = $m_header;
    }

    public function addTag($name, $value)
    {
        $this->header->addTag('meta', [
            'property' => $name,
            'content' => $value
        ], true);
    }

}
