<?php namespace EC\SEO;
defined('_ESPADA') or die(NO_ACCESS);

require(__DIR__ . '/../3rdparty/php-publisher/library/Publisher.php');

use E, EC,
    pubsubhubbub\publisher\Publisher;

class HRss {

    static private $RssItemFields = [ 'title', 'link', 'description', 'pubDate' ];

    static public function GetFilePath()
    {
        return PATH_TMP . '/rss.xml';
    }

    static public function GetFileUri()
    {
        return SITE_DOMAIN . URI_TMP . 'rss.xml';
    }

    static public function Update($rss_item_infos)
    {
        foreach ($rss_item_infos as $rss_item_info) {
            $rss_item = [];

            foreach (self::$RssItemFields as $rss_item_field_name) {
                $rss_item[$rss_item_field_name] =
                        $rss_item_info[$rss_item_field_name];
            }
        }

        $save_error = self::Save($rss_item_infos);
        if ($save_error !== null)
            return $save_error;

        /* Notify hubs */
        try {
            $hub_url = "http://pubsubhubbub.appspot.com/";
            $p = new Publisher($hub_url);
            $topic_url = SITE_DOMAIN;

            if ($p->publish_update($topic_url))
                return null;
            else
                return print_r($p->last_response(), true);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    static private function Save($rss_items)
    {
        $file_path = PATH_TMP . '/rss.xml';

        $xml_items = '';
        foreach ($rss_items as $rss_item) {
            $xml_items .=
                '<item>' .
                    '<title>' . $rss_item['title'] . '</title>' .
                    '<link>' . $rss_item['link'] . '</link>' .
                    '<guid>' . $rss_item['link'] . '</guid>' .
                    '<description>' . $rss_item['description'] . '</description>' .
                    '<pubDate>' .
                        date('r', $rss_item['pubDate']) .
                    '</pubDate>' .
                '</item>';
        }

        $xml =
            '<?xml version="1.0" encoding="UTF-8" ?>' .
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' .
                '<channel>' .
                    '<atom:link href="' . HRss::GetFileUri() . '" rel="self"' .
                            ' type="application/rss+xml" />' .
                    '<title>' .
                        EC\HConfig::GetRequired('site_Title') .
                    '</title>' .
                    '<link>' . SITE_DOMAIN . '</link>' .
                    '<description>' .
                        EC\HConfig::GetRequired('site_Description') .
                    '</description>' .
                    $xml_items .
                '</channel>' .
            '</rss>';

        try {
            $fp = fopen($file_path, "w+");
            if (flock($fp, LOCK_EX)) {
                fwrite($fp, $xml);
                flock($fp, LOCK_UN);
            }

            fclose($fp);

            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
