<?php namespace EC\ELibs;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Basic\MHead;
use EC\Text\HText;
use Exception;
use Override;

class MELibs extends E\Module {
    private MHead $head;

    private string $scriptCSPHash;

    private array $fields = [];
    private array $fieldFns = [];
    private array $texts = [];
    private string $script = '';

    private bool $fieldsInitialized;

    public function __construct(E\Site $site, MHead|null $head) {
        parent::__construct($site);

        $site->addM($this);

        $this->head = $head;
        if ($this->head !== null)
            $this->scriptCSPHash = $head->generateScriptCSPHash();

        $this->fieldsInitialized = false;
    }

    public function addScript(string $script) {
         $this->requireBeforePreDisplay();

        $this->script .= $script;
    }

    public function addTexts(array $texts) {
         $this->requireBeforePreDisplay();

        $this->texts = array_merge($this->texts, $texts);
    }

    public function addTranslations(string $path) {
         $this->requireBeforePreDisplay();

        $pkg = explode(':', $path)[0];
        $texts = [];
        $translationsArr = HText::GetTranslations($pkg)->getArray();

        foreach ($translationsArr as $text => $textTranslation) 
            $texts["{$pkg}:{$text}"] = $textTranslation;

        $this->addTexts($texts);
    }

    public function addTranslations_As(string $prefixName, string $path) {
         $this->requireBeforePreDisplay();

        $texts = [];
        $translationsArr = HText::GetTranslations($path)->getArray();

        foreach ($translationsArr as $text => $textTranslation) 
            $texts["{$prefixName}:{$text}"] = $textTranslation;

        $this->addTexts($texts);
    }

    public function setField(string $fieldName, mixed $fieldValue) {
        $this->requireBeforePreDisplay();

        $this->fields[$fieldName] = $fieldValue;
    } 

    public function setFieldFn(string $fieldName, \Closure $fieldFn) {
         $this->requireBeforePreDisplay();

        $this->fieldFns[$fieldName] = $fieldFn;
    }

    public function getFields() {
        $this->initFields();

        return $this->fields;;
    }

    public function getScript() {
        $fields_JSON =  json_encode($this->getFields());
        if ($fields_JSON === false)
            throw new \Exception('Cannot encode fields to JSON: ' . json_last_error_msg());

        $fieldsString = str_replace("'", "\\'", $fields_JSON);
        $textsString = str_replace("'", "\\'", json_encode($this->texts));

        $date_Formats_Date = HText::_('ELibs:date_Formats_Date');
        $date_Formats_DateTime = HText::_('ELibs:date_Formats_DateTime');
        $date_Formats_Time = HText::_('ELibs:date_Formats_Time');

        $script = <<<SCRIPT
    (function() {
        let abDate = jsLibs.require('ab-date');
        let eLibs = jsLibs.require('e-libs');

        abDate.formats_Date = '{$date_Formats_Date}';
        abDate.formats_DateTime = '{$date_Formats_DateTime}';
        abDate.formats_Time = '{$date_Formats_Time}';

        eLibs.eFields.add({$fieldsString});
        eLibs.eTexts.add({$textsString});

        {$this->script}
    })();
SCRIPT;

        return $script;
    }


    private function initFields() {
        if (!$this->fieldsInitialized)
            throw new Exception("Fields already initialized.");
        $this->fieldsInitialized = true;

        /* Defaults */
        $uris = [
            'base' => E\Uri::Base(),
            'pages' => [],
        ];
        $pages = E\Pages::GetAll();
        foreach ($pages as $page)
            $uris['pages'][$page->getName()] = str_replace('*', '', $page->getUri_Raw(''));
        $this->setField('eUris', $uris);

        $this->addTranslations('Date');
        $this->setField('eLang', E\Langs::Get());

        /* Setup */
        foreach ($this->fieldFns as $fieldName => $fieldFn) {
            $this->setField($fieldName, $fieldFn());
        }
    }


    #[Override]
    public function _preDisplay(E\Site $site): void {
        if ($this->head !== null) {
            $site->addL('postBody', new EC\Basic\LScript(function() {
                return $this->getScript(); }, $this->scriptCSPHash));
        }   
    }
}