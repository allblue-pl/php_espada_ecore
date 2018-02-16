<?php namespace EC\PDF;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CReader
{

    private $pages = null;
    private $fields = null;

    public function __construct()
    {

    }

    public function getFields()
    {
        return $this->fields;
    }

    // public function getPages()
    // {
    //     return $this->pages;
    // }

    public function read($file_path)
    {
        include(__DIR__ . '/../3rdparty/PDFParser/vendor/autoload.php');

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($file_path);

        $pages = $pdf->getPages();
        foreach ($pages as $page)
            print_r($page->getTextArray());

        if (!file_exists($file_path))
            return false;

        $this->pages = [];
        $content = file_get_contents($file_path);

        $this->parseText($content);

        return true;
    }

    private function parseText($content)
    {
        /**
         * Split apart the PDF document into sections. We will address each
         * section separately.
         */
        $content_data_array = $this->getDataArray($content, 'obj', 'endobj');

        $j = 0;
        $chunks = [];

        /**
         * Attempt to extract each part of the PDF document into a 'filter'
         * element and a 'data' element. This can then be used to decode the
         * data.
         */
        $j = 0;
        foreach ($content_data_array as $content_data) {
            $filter_data_array = $this->getDataArray($content_data, '<<', '>>');

            for ($k = 0; $k < count($filter_data_array); $k++) {
                $chunks[$j]['filter'] = $filter_data_array[$k];
                // print_r($filter_data_array[$k]);
                // echo "\r\n\r\n##############\r\n";

                $stream_data_array = $this->getDataArray($content_data,
                        'stream', 'endstream');

                if (count($stream_data_array) > 0)
                    $chunks[$j]['data'] = $this->trimStreamData($stream_data_array);

                $j++;

                break;
            }
        }

        $result_data = [];
        $this->fields = null;
        $i = 0;
        /* <Debug> */
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);
        // /* </Debug> */

        $count = 0;
        foreach ($chunks as $chunk) {
            if (isset($chunk['data'])) {
                if (strpos($chunk['filter'], 'FlateDecode') !== false) {
                    $chunk_data = $chunk['data'];
                    $bytes = unpack('C*', $chunk_data);
                    $chunk_data = implode(array_map("chr", $bytes));
                    $data = gzuncompress($chunk_data);

                    if ($count === 8) {
                        echo "Test: " . count($bytes) . "\r\n";
                        print_r($chunk['filter']);
                        echo "\r\n\r\n##########\r\n\r\n";
                        // print_r($data);
                        // echo "\r\n\r\n##############\r\n";
                    }

                    if ($data != '' && $count === 8) {
                        $result_data[] = $this->parseTextElements($data);
                    }

                    $count++;
                } else
                    $data = $chunk['data'];
                //
                // if ($data != '' && $count === 8) {
                //     $result_data[] = $this->parseTextElements($data);
                // }

                $i++;
            }
        }

        return $result_data;
    }

    private function parseTextElements($data)
    {
        if (strpos($data, '/CIDInit') === 0)
            return [];

        $texts = [];
        $lines = explode("\n", $data);

        foreach ($lines as $line) {
            $line = trim($line);
            $matches = [];

            if (preg_match('/^(?<command>.*[\)\] ])(?<operator>[a-z]+[\*]?)$/i',
                           $line, $matches)) {
                print_r($matches);
                $command = trim($matches['command']);

                $found_octal_values = [];
                preg_match_all('/\\\\([0-9]{3})/', $command, $found_octal_values);

                foreach($found_octal_values[0] as $value) {
                    $octal = substr($value, 1);

                    if (intval($octal) < 40)
                        $command = str_replace($value, '', $command);
                    else
                        $command = str_replace($value, chr(octdec($octal)), $command);
                }

                $command = preg_replace('/\\\\[\r\n]/', '', $command);
                $command = preg_replace('/\\\\[rnftb ]/', ' ', $command);
                // Force UTF-8 charset
                $encoding = mb_detect_encoding($command,
                    array('ASCII', 'UTF-8', 'Windows-1252', 'ISO-8859-1'));
                if (strtoupper($encoding) != 'UTF-8') {
                    if ($decoded = @iconv('CP1252', 'UTF-8//TRANSLIT//IGNORE', $command))
                        $command = $decoded;
                }

                $operator = trim($matches['operator']);
            } else {
                $command = $line;
                $operator = '';
            }

            $texts[] = "#######" . $operator;

            /* Parse fields. */
            if (preg_match('/\((?<field>.*?)\) (Tj)|(TJ)/i', $line, $matches))
                $this->fields[] = $matches['field'];

            echo $operator . ": " . $command . "\r\n";

            // Handle main operators
            switch ($operator) {
                // Set character spacing.
                case 'Tc':
                    break;

                // Move text current point.
                case 'Td':
                    $values = explode(' ', $command);
                    $y = array_pop($values);
                    $x = array_pop($values);
                    if ($x > 0) {
                      // $text .= ' ';
                    }
                    if ($y < 0) {
                      // $text .= ' ';
                    }
                    break;

                // Move text current point and set leading.
                case 'TD':
                    $values = explode(' ', $command);
                    $y = array_pop($values);
                    if ($y < 0) {
                      // $text .= "\n";
                    }
                    break;
                // Set font name and size.
                case 'Tf':
                    /* $text .= ' '; */
                    break;
                // Display text, allowing individual character positioning
                case 'TJ':
                    // $start = mb_strpos($command, '[', null, 'UTF-8') + 1;
                    // $end   = mb_strrpos($command, ']', null, 'UTF-8');
                    // $text = self::parseTextCommand(mb_substr($command, $start, $end - $start, 'UTF-8'));
                    // $texts[] = $text;
                    // if ($this->fields !== null)
                    //     $this->fields[] = $text;
                    break;

                // Display text.
                case 'Tj':
                    // $start = mb_strpos($command, '(', null, 'UTF-8') + 1;
                    // $end   = mb_strrpos($command, ')', null, 'UTF-8');
                    // $text = mb_substr($command, $start, $end - $start, 'UTF-8'); // Removes round brackets
                    // $texts[] = $text;
                    // if ($this->fields !== null)
                    //     $this->fields[] = $text;
                    break;

                // Set leading.
                case 'TL':
                // Set text matrix.
                case 'Tm':
                // $text.= ' ';
                    break;
                // Set text rendering mode.
                case 'Tr':
                    break;
                // Set super/subscripting text rise.
                case 'Ts':
                    break;
                // Set text spacing.
                case 'Tw':
                    break;

                // Set horizontal scaling.
                case 'Tz':
                    break;

                // Move to start of next line.
                case 'T*':
                    // $text.= "\n";
                    break;

                // Internal use
                case 'g':
                case 'gs':
                case 're':
                case 'f':
                // Begin text
                case 'BT':
                // End text
                case 'ET':
                    break;
                case 'rg':
                    // if ($this->fields !== null)
                    //     $this->pages[] = $this->fields;
                    // $this->fields = [];
                case '':
                    break;

                default:
            }
        }

        return $texts;
    }

    private function parseTextCommand($text, $font_size = 0)
    {
        $result = '';
        $cur_start_pos = 0;

        while (($cur_start_text = mb_strpos($text, '(', $cur_start_pos, 'UTF-8')) !== false) {
          // New text element found
          if ($cur_start_text - $cur_start_pos > 8) {
            $spacing = ' ';
          } else {
            $spacing_size = mb_substr($text, $cur_start_pos, $cur_start_text - $cur_start_pos, 'UTF-8');

            if ($spacing_size < -50) {
              $spacing = ' ';
            } else {
              $spacing = '';
            }
          }
          $cur_start_text++;

          $start_search_end = $cur_start_text;
          while (($cur_start_pos = mb_strpos($text, ')', $start_search_end, 'UTF-8')) !== false) {
            if (mb_substr($text, $cur_start_pos - 1, 1, 'UTF-8') != '\\') {
              break;
            }
            $start_search_end = $cur_start_pos + 1;
          }

          // something wrong happened
          if ($cur_start_pos === false) {
            break;
          }

          // Add to result
          $result .= $spacing . mb_substr($text, $cur_start_text, $cur_start_pos - $cur_start_text, 'UTF-8');
          $cur_start_pos++;
        }

        return $result;
      }

    private function getDataArray($data, $start_word, $end_word)
    {
        $results = [];

        $end = -mb_strlen($end_word);
        while (true) {
            $start = strpos($data, $start_word, $end + mb_strlen($end_word));
            $end = strpos($data, $end_word, $start + mb_strlen($start));

            if ($end === false || $start === false)
                break;

            $results[] = substr($data, $start, $end - $start + strlen($end_word));
        }

        return $results;
    }

    private function trimStreamData($data)
    {
        $stream = $data[0];

        $start_string = "stream\r\n";
        if (strpos($stream, $start_string) !== 0)
            $start_string = "stream\n";

        $end_string = "\r\nendstream";

        if (strrpos($stream, $end_string) !== strlen($stream) - strlen($end_string)) {
            $end_string = "\nendstream";

            if (strrpos($stream, $end_string) !== strlen($stream) - strlen($end_string))
                $end_string = "endstream";
        }

        $t_data = substr($data[0], strlen($start_string),
                strlen($data[0]) - strlen($start_string) - strlen($end_string));

        return $t_data;

        // return str_replace(array("\r\n"), array(''),
        //         substr($data[0], strlen("stream\r\n"),
        //         strlen($data[0]) - strlen("stream\r\n") - strlen("\r\nendstream")));
    }

}
