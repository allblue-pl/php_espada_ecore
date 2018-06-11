<?php namespace EC\ELibs;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MELibs extends E\Module
{

    private $header = null;

    private $fields = [];
    private $texts = [];

    function __construct(EC\Basic\MHeader $header)
    {
        $this->header = $header;
    }

    function addTexts($texts)
    {
        $this->texts = array_merge($this->texts, $texts);
    }

    function setField($fieldName, $fieldValue)
    {
        $this->fields[$fieldName] = $fieldValue;
    } 

    function _postInitialize(E\Site $site)
    {
        $fieldsString = str_replace("'", "\\'", json_encode($this->fields));
        $textsString = str_replace("'", "\\'", json_encode($this->texts));

        $script = <<<HTML
<script type="text/javascript">
    (() => {
        let eLibs = jsLibs.require('e-libs');

        eLibs.eFields.add({$fieldsString});
        eLibs.eTexts.add({$textsString});
    })();
</script>
HTML;
        
        $this->header->addHtml($script);
    }

}