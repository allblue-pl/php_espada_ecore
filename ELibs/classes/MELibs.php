<?php namespace EC\ELibs;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MELibs extends E\Module
{

    private $header = null;

    private $fields = [];
    private $texts = [];
    private $script = '';

    function __construct(EC\Basic\MHeader $header)
    {
        $this->header = $header;
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

    function setField($fieldName, $fieldValue)
    {
        $this->requireBeforePreDisplay();

        $this->fields[$fieldName] = $fieldValue;
    } 

    function _preDisplay(E\Site $site)
    {
        /* Defaults */
        $this->addTranslations('Date');
        $this->setField('eLang', E\Langs::Get());

        /* Setup */
        $fieldsString = str_replace("'", "\\'", json_encode($this->fields));
        $textsString = str_replace("'", "\\'", json_encode($this->texts));

        $date_UTCOffset = EC\HDate::GetUTCOffset();
        $date_Formats_Date = EC\HText::_('ELibs:date_Formats_Date');
        $date_Formats_DateTime = EC\HText::_('ELibs:date_Formats_DateTime');
        $date_Formats_Time = EC\HText::_('ELibs:date_Formats_Time');

        $script = <<<HTML
<script type="text/javascript">
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
</script>
HTML;
        
        $this->header->addHtml($script);
    }

}