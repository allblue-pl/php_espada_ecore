<?php namespace EC\PDF;
defined('_ESPADA') or die(NO_ACCESS);

include(__DIR__ . '/../3rdparty/pdfparser/vendor/autoload.php');

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
            foreach ($page_fields as $field) {
                // echo EC\Strings\HEncoding::Convert($field, 'utf-8', 'windows-1250') . "\r\n";
                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function getFields_Hex()
    {
        $fields = [];

        $pages = $this->pdf->getPages();
        foreach ($pages as $page) {
            $page_fields = $page->extractRawData();
            foreach ($page_fields as $field) {
                if ($field['o'] === 'Tj')
                    $fields[] = $field['c'];
            }
        }

        return $fields;
    }

    public function getTest()
    {
        $fields = [];

        $pages = $this->pdf->getPages();
        foreach ($pages as $page) {
            print_r($page->getTextArray());
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

    // public function getFields()
    // {
    //     var_dump($this->pdf);
    //     die;
    // }

    // public function read($filePath)
    // {
    //     $pdf_Raw = file_get_contents($filePath);
    //     if ($pdf_Raw === false)
    //         return false;

    //     $this->parser = new \Com\Tecnick\Pdf\Parser\Parser([ 
    //         'ignore_filter_errors' => true, 
    //     ]);
    //     $this->pdf = $this->parser->parse($pdf_Raw);

    //     return true;
    // }

}
