<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MHeader extends \E\Module
{

    private $fields = null;

    /* Meta Data */
    private $title = 'Espada Website';
    private $description = '';
    private $keywords = '';
    private $author = null;

    /* Scripts */
    private $scripts = '';

    /* Tags */
    private $tags = '';

    /* Other */
    private $html = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function addHtml($html)
    {
        $this->html .= $html . "\r\n";
    }

    public function addScript($uri)
    {
        if (EDEBUG)
            $uri .= "?v=" . EC\HHash::Generate(8);
        else
            $uri .= "?v=" . EC\HConfig::Get('Config', 'version', '1');

        $this->html .= $this->getNode('script', [
            "type" => "text/javascript",
            "src" => $uri
        ]);
    }

    public function addTag($name, $attribs = [], $self_closing = false,
            $value = '')
    {
        $this->html .= $this->getNode($name, $attribs, $self_closing, $value) .
                "\n";
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    protected function _preInitialize(E\Site $site)
    {
        $site->addL('header', E\Layout::_('Basic:raw', function() {
            $header = '';

            /* Meta Data */
            /* Title */
            $header .= $this->getNode('title', [], false, $this->title);

            /* Meta Description */
            $header .= $this->getNode('meta', [
                    "name" => "description",
                    "content" => $this->description
                ], true) . "\n";

            /* Meta Keywords */
            $header .= $this->getNode('meta', [
                    "name" => "keywords",
                    "content" => $this->keywords
                ], true) . "\n";

            /* Author */
            if ($this->author !== null) {
                $header .= $this->getNode('meta', [
                        "name" => "author",
                        "content" => $this->author
                    ], true) . "\n";
            }


            /* Html (Scripts, Tags) */
            $header .= $this->html;

            return [
                'raw' => $header
            ];
        }));
    }

    private function getNode($name, $attribs = [], $self_closing = false,
        $value = '')
    {
        /* Open Tag */
        $node = '<' . $name;
        foreach ($attribs as $attrib_name => $attrib_value) {
            $node .= ' ' . $attrib_name . '="' . $attrib_value . '"';
        }

        /* Is Self Closing */
        if ($self_closing)
            return $node . ' />';
        $node .= '>';

        /* Value */
        $node .= $value;

        /* Close Tag */
        return $node . '</' . $name . '>' . "\r\n";
    }

}
