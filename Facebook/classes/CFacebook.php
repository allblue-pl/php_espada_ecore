<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);


class CFacebook
{

    private $config = null;

    public function __construct(\EC\MConfig $config)
    {
        $this->config = $config;
    }

    public function getCommentHash()
    {
        return $this->config->salt;
    }

}
