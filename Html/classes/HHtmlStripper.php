<?php namespace EC\Html;
defined('_ESPADA') or die(NO_ACCESS);

require_once(PATH_ESITE . "/composer/vendor/autoload.php");

use E, EC;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_HTMLDefinition;

class HHtmlStripper {
    static public function Strip(string $html): string {
        $config = HTMLPurifier_Config::createDefault();

        $config->set('HTML.AllowedElements', [
            'p', 'br', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'strong', 'b', 'em', 'i', 'u', 'strike', 's', 'del', 'ins', 'sub', 'sup',
            'blockquote', 'pre', 'code', 'address',
            'ul', 'ol', 'li', 'dl', 'dt', 'dd',
            'a',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
            'img', 'video', 'audio', 'source', 'picture', 'figure', 'figcaption', 'iframe'
        ]);

        $config->set('HTML.AllowedAttributes', [
            '*.class', '*.style', '*.id', '*.dir',
            'a.href', 'a.target', 'a.rel', 'a.title', 'a.download',
            'ol.start',
            'table.border', 'table.cellpadding', 'table.cellspacing',
            'td.rowspan', 'td.colspan', 'td.scope',
            'th.rowspan', 'th.colspan', 'th.scope',
            'img.src', 'img.alt', 'img.title', 'img.width', 'img.height',
            'video.controls', 'video.poster', 'video.preload', 'video.autoplay', 'video.loop', 'video.muted', 'video.width', 'video.height',
            'audio.controls', 'audio.preload', 'audio.autoplay', 'audio.loop', 'audio.muted',
            'source.src', 'source.type',
            'iframe.src', 'iframe.width', 'iframe.height', 'iframe.frameborder', 'iframe.allowfullscreen'
        ]);

        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%');

        $def = $config->getHTMLDefinition(true);
        self::AddCustomDefs($def);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }


    static private function AddCustomDefs(HTMLPurifier_HTMLDefinition $def) {
        /* Attributes */
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('a', 'download', 'CDATA');

        /* Elements */
        $def->addElement(
            'figure',
            'Block',
            'Flow',
            'Common'
        );
        $def->addElement(
            'figcaption',
            'Block',
            'Flow',
            'Common'
        );
        $def->addElement(
            'picture',
            'Inline',
            'Custom: (source*, img)',
            'Common'
        );
        $def->addElement(
            'video',
            'Block',
            'Flow',
            'Common',
            [ 
                'src'      => 'URI',
                'poster'   => 'URI',
                'preload'  => 'Enum#auto,metadata,none',
                'controls' => 'Bool',
                'autoplay' => 'Bool',
                'loop'     => 'Bool',
                'muted'    => 'Bool',
                'width'    => 'Length',
                'height'   => 'Length'
            ]
        );
        $def->addElement(
            'audio',        
            'Block',        
            'Flow',         
            'Common',       
            [               
                'src'      => 'URI',
                'preload'  => 'Enum#auto,metadata,none',
                'controls' => 'Bool',
                'autoplay' => 'Bool',
                'loop'     => 'Bool',
                'muted'    => 'Bool'
            ]
        );
        $def->addElement(
            'source',
            'Block',
            'Flow',
            'Common',
            [
                'src'  => 'URI',
                'type' => 'CDATA'
            ]
        );
    }
}