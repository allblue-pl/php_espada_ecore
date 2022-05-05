<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MHead extends E\Module
{

    private $fields = null;

    private $csp = null;
    private $csp_ScriptSrc = null;
    private $scriptCSPHashes = [];
    private $styleCSPHashes = [];

    /* Meta Data */
    private $title = 'Espada Website';
    private $description = '';
    private $keywords = [];
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

    public function setContentSecurityPolicy(string $contentSecurityPolicy)
    {
        $this->csp = $contentSecurityPolicy;
    }

    public function setContentSecurityPolicy_ScriptSrc(string $scriptSrc)
    {
        $this->csp_ScriptSrc = $scriptSrc;
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

    public function addKeywords($keywords)
    {
        $this->keywords = array_merge($this->keywords, explode(',', $keywords));
    }

    public function addTag($name, $attribs = [], $self_closing = false,
            $value = '')
    {
        $this->html .= $this->getNode($name, $attribs, $self_closing, $value) .
                "\n";
    }

    public function generateScriptCSPHash()
    {
        $this->requireBeforePostInitialize();

        $hash = EC\HHash::Generate(16);
        $this->scriptCSPHashes[] = "'nonce-{$hash}'";

        return $hash;
    }

    public function generateStyleCSPHash()
    {
        $this->requireBeforePostInitialize();

        $hash = EC\HHash::Generate(16);
        $this->styleCSPHashes[] = "'nonce-{$hash}'";

        return $hash;
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
        $this->keywords = explode(',', $keywords);
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    protected function _postInitialize(E\Site $site)
    {
        if ($this->csp !== null) {
            header("Content-Security-Policy: {$this->csp}" . 
                    " script-src 'self' 'unsafe-eval' " . 
                    ($this->csp_ScriptSrc === null ? '' : $this->csp_ScriptSrc . ' ') .
                    implode(' ', $this->scriptCSPHashes) . ';' .
                    " style-src 'self' " . implode(' ', $this->styleCSPHashes) . ';');
        }
    }

    protected function _preDisplay(E\Site $site)
    {
        $site->addL('postHead', E\Layout::_('Basic:raw', function() {
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
            $keywords = [];
            foreach ($this->keywords as $keyword)
                $keywords[] = trim($keyword);
            $header .= $this->getNode('meta', [
                    "name" => "keywords",
                    "content" => implode(', ', $keywords),
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
