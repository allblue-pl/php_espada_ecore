<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Config\HConfig;
use EC\Hash\HHash;

class MHead extends E\Module {
    // private $fields = null;

    private ?string $csp = null;
    private ?String $csp_ScriptSrc = null;
    private array $scriptCSPHashes = [];
    private array $styleCSPHashes = [];

    /* Meta Data */
    private string $title = 'Espada Website';
    private string $description = '';
    private array $keywords = [];
    private ?string $author = null;

    // /* Scripts */
    // private $scripts = '';

    // /* Tags */
    // private $tags = '';

    /* Other */
    private $html = '';

    public function __construct(E\Site $site) {
        parent::__construct($site);
    }

    public function setContentSecurityPolicy(string $contentSecurityPolicy) {
        $this->csp = $contentSecurityPolicy;
    }

    public function setContentSecurityPolicy_ScriptSrc(string $scriptSrc) {
        $this->csp_ScriptSrc = $scriptSrc;
    }

    public function addHtml(string $html) {
        $this->html .= $html . "\r\n";
    }

    public function addScript(string $uri) {
        if (EDEBUG)
            $uri .= "?v=" . HHash::Generate(8);
        else
            $uri .= "?v=" . HConfig::Get('Config', 'version', '1');

        $this->html .= $this->getNode('script', [
            "type" => "text/javascript",
            "src" => $uri
        ]);
    }

    public function addKeywords(string $keywords) {
        $this->keywords = array_merge($this->keywords, explode(',', $keywords));
    }

    public function addTag(string $name, array $attribs = [], 
            bool $selfClosing = false, string $value = '') {
        $this->html .= $this->getNode($name, $attribs, $selfClosing, $value) .
                "\n";
    }

    public function generateScriptCSPHash() {
        $this->requireBeforePostInitialize();

        $hash = HHash::Generate(16);
        $this->scriptCSPHashes[] = "'nonce-{$hash}'";

        return $hash;
    }

    public function generateStyleCSPHash() {
        $this->requireBeforePostInitialize();

        $hash = HHash::Generate(16);
        $this->styleCSPHashes[] = "'nonce-{$hash}'";

        return $hash;
    }

    public function setAuthor(string $author) {
        $this->author = $author;
    }

    public function setDescription(string $description) {
        $this->description = $description;
    }

    public function setKeywords(string $keywords) {
        $this->keywords = explode(',', $keywords);
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }

    protected function _postInitialize(E\Site $site): void {
        if ($this->csp !== null) {
            header("Content-Security-Policy: {$this->csp}" . 
                    " script-src 'self' 'unsafe-eval' " . 
                    ($this->csp_ScriptSrc === null ? '' : $this->csp_ScriptSrc . ' ') .
                    implode(' ', $this->scriptCSPHashes) . ';' .
                    " style-src 'self' " . implode(' ', $this->styleCSPHashes) . ';');
        }
    }

    protected function _preDisplay(E\Site $site): void {
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

    private function getNode(string $name, array $attribs = [], 
            bool $self_closing = false,
        $value = '') {
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
