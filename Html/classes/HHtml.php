<?php namespace EC\Html;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HHtml {

    static public function A($content, $href) {
        return "<a href=\"{$href}\">{$content}</a>";
    }

    static public function Elem($name, $content = "", $attrs = []) {
        $html = "<{$name}";
        foreach ($attrs as $attr_name => $attr_val)
            $html .= " {$attr_val}";
        $html .= ">{$content}</{$name}";

        return $html;
    }

    static public function Img($src, $alt) {
        return "<img src=\"{$src}\" alt=\"{$alt}\" />";
    }

}
