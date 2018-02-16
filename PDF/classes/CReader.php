<?php namespace EC\PDF;
defined('_ESPADA') or die(NO_ACCESS);

include(__DIR__ . '/../3rdparty/PDFParser/vendor/autoload.php');

use E, EC;

class CReader
{

    private $parser = null;
    private $pdf = null;

    public function __construct()
    {
        $this->parser = new \Smalot\PdfParser\Parser();
    }

    public function getFields()
    {
        $fields = [];

        $pages = $this->pdf->getPages();
        foreach ($pages as $page) {
            $page_fields = $page->getTextArray();
            foreach ($page_fields as $field)
                $fields[] = $field;
        }

        return $fields;
    }

    // public function getPages()
    // {
    //     return $this->pages;
    // }

    public function read($file_path)
    {
        if (!file_exists($file_path))
            return false;

        $this->pdf = $this->parser->parseFile($file_path);

        return true;
    }

}
