<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CSitemap {

    private $urls = null;

    public function __construct()
    {
        $this->urls = [];
    }

    public function addUrl(CUrl $url)
    {
        $this->urls[] = $url;
    }

    public function getXML()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
        
        foreach ($this->urls as $url) {
            $xml .= '  <url>' . "\r\n";
            $xml .= '    <loc>' . $url->loc . '</loc>' . "\r\n";
            if ($url->lastMod !== null)
                $xml .= '    <lastmod>' . $url->lastMod . '</lastmod>' . "\r\n";
            if ($url->changeFreq !== null)
                $xml .= '    <changefreq>' . $url->changeFreq . '</changefreq>' . "\r\n";
            $xml .= '  </url>' . "\r\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

}