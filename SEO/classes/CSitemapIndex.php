<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CSitemapIndex {

    private $sitemaps = null;

    public function __construct() {
        $this->sitemaps = [];
    }

    public function addSitemap($loc, $lastMod = null) {
        $this->sitemaps[] = [
            'loc' => $loc,
            'lastMod' => $lastMod,
        ];
    }

    public function getXML() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
        
        foreach ($this->sitemaps as $sitemap) {
            $xml .= '  <sitemap>' . "\r\n";
            $xml .= '    <loc>' . $sitemap['loc'] . '</loc>' . "\r\n";
            if ($sitemap['lastMod'] !== null) {
                $xml .= '    <lastmod>' . gmdate(DATE_W3C, $sitemap['lastMod']) . 
                        '</lastmod>' . "\r\n";
            }
            $xml .= '  </sitemap>' . "\r\n";
        }
        $xml .= '</sitemapindex>';

        return $xml;
    }

}