<?php namespace EC\ELibs;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MELibs extends E\Module
{

    private $head = null;
    private $scriptCSPHash = null;

    private $fields = [];
    private $fieldFns = [];
    private $texts = [];
    private $script = '';

    function __construct(EC\Basic\MHead $head)
    {
        $this->head = $head;
        $this->scriptCSPHash = $head->generateScriptCSPHash();
    }

    function addScript(string $script)
    {
        $this->script .= $script;
    }

    function addTexts(array $texts)
    {
        $this->texts = array_merge($this->texts, $texts);
    }

    function addTranslations($path)
    {
        $pkg = explode(':', $path)[0];
        $texts = [];
        $translationsArr = EC\HText::GetTranslations($pkg)->getArray();

        foreach ($translationsArr as $text => $textTranslation) 
            $texts["{$pkg}:{$text}"] = $textTranslation;

        $this->addTexts($texts);
    }

    function addTranslations_As($prefixName, $path)
    {
        $texts = [];
        $translationsArr = EC\HText::GetTranslations($path)->getArray();

        foreach ($translationsArr as $text => $textTranslation) 
            $texts["{$prefixName}:{$text}"] = $textTranslation;

        $this->addTexts($texts);
    }

    function setField($fieldName, $fieldValue)
    {
        // $this->requireBeforePreDisplay();

        $this->fields[$fieldName] = $fieldValue;
    } 

    function setFieldFn($fieldName, $fieldFn)
    {
        $this->fieldFns[$fieldName] = $fieldFn;
    }

    function _preDisplay(E\Site $site)
    {
        $site->addL('postBodyInit', new EC\Basic\LScript(function() {
            return $this->getScript(); }, $this->scriptCSPHash));
    }

    function getScript()
    {
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

        $fields_JSON =  json_encode($this->fields);
        if ($fields_JSON === false)
            throw new \Exception('Cannot encode fields to JSON: ' . json_last_error_msg());

        $fieldsString = str_replace("'", "\\'", $fields_JSON);
        $textsString = str_replace("'", "\\'", json_encode($this->texts));

        $date_UTCOffset = EC\HDate::GetUTCOffset();
        $date_Formats_Date = EC\HText::_('ELibs:date_Formats_Date');
        $date_Formats_DateTime = EC\HText::_('ELibs:date_Formats_DateTime');
        $date_Formats_Time = EC\HText::_('ELibs:date_Formats_Time');

        $script = <<<SCRIPT
    (function() {
        let abDate = jsLibs.require('ab-date');
        let eLibs = jsLibs.require('e-libs');

        abDate.utfOffset = {$date_UTCOffset};
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

}