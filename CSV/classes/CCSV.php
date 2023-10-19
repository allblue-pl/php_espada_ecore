<?php namespace EC\CSV;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CCSV
{

    private $separators = [ ';', ',' ];
    private $charset = '';
    private $hasQuotedRows = true;

    private $separator = '';

    private $file = null;
    private $textArr = null;
    private $textArr_Current = 0;


    private $line_Index = -1;
    private $line0 = null;
    private $line1 = null;

    public function __construct(array $options = [])
    {
        if (array_key_exists('separators', $options)) {
            $this->separators = $options['separators'];
        }
        if (array_key_exists('charset', $options)) {
            $this->charset = $options['charset'];
        }
        if (array_key_exists('hasQuotedRows', $options)) {
            $this->hasQuotedRows = $options['hasQuotedRows'];
        }
    }

    public function close()
    {
        if ($this->file !== null)
            fclose($this->file);
        $this->file = null;

        $this->line0 = null;
        $this->line1 = null;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function open($file_path)
    {
        if ($this->file !== null)
            throw new \Exception('Close `CSV` before opening.');

        if (!file_exists($file_path))
            return false;

        $this->file = fopen($file_path, 'r');
        if ($this->file === false) {
            $this->close();
            return false;
        }

        $this->line0 = null;
        $this->line1 = null;

        $this->line0 = $this->getNextLine_Helper();
        if ($this->line0 === null)
            return true;

        $this->line1 = $this->getNextLine_Helper();

        $this->determineSeparator();

        return true;
    }

    public function openText($text)
    {
        $text = str_replace("\r\n", "\n", $text);
        $this->textArr = explode("\n", $text);

        $this->line0 = $this->getNextLine_Helper();
        if ($this->line0 === null)
            return;
        $this->line1 = $this->getNextLine_Helper();

        $this->determineSeparator();
    }

    public function nextRow()
    {
        $line = $this->getNextLine();
        if ($line === null)
            return null;

        $row = new CRow();

        $index = 0;
        $quoted = false;
        $quoteSign = '"';
        $column = null;

        while(true) {
            if ($index >= mb_strlen($line)) {
                if ($quoted) {
                    $line_Next = $this->getNextLine();
                    if ($line_Next === null) {
                        $row->addColumn($column === null ? '' : $column);
                        break;
                    } else if ($line_Next === '') {
                        continue;
                    } else {
                        $line .= "\r\n" . $line_Next;
                    }
                } else {
                    $row->addColumn($column === null ? '' : $column);
                    break;
                }
            }

            /* Char */
            $escaped = false;
            $char = mb_substr($line, $index, 1);
            $index++;
            $char_Next = $index < mb_strlen($line) ? 
                    mb_substr($line, $index, 1) : null;

            if ($char === '\\') {
                if ($char_Next !== null) {
                    $escaped = true;
                    $char = $char_Next;
                    $index++;
                }
            }
            /* / Char */

            if ($escaped) {
                $column .= $char;
                continue;
            }

            if ($column === null) {
                $column = '';

                if ($char === $quoteSign && $this->hasQuotedRows) {
                    $quoted = true;
                    continue;
                }
            }

            if ($quoted) {
                if ($char === $quoteSign && ($char_Next === $this->separator || 
                        $char_Next === null)) {
                    // echo "Here?";
                    if ($char_Next === $this->separator)
                        $index++;

                    $row->addColumn($column === null ? '' : $column);
                    $column = null;
                    $quoted = false;
                    continue;
                }
            } else {
                if ($char === $this->separator) {
                    $row->addColumn($column === null ? '' : $column);
                    $column = null;
                    continue;
                }
            }

            $column .= $char;
        }

        return $row;
    }


    private function determineSeparator()
    {
        if (count($this->separators) === 1) {
            $this->separator = $this->separators[0];
            return;
        }

        $line0 = $this->line0;
        $line1 = $this->line1;

        if ($line0 === null || $line1 === null)
            throw new \Exception('Cannot determine separator without second line.');

        $this->separator = null;
        $max_count = -1;
        if ($line1 !== null) {
            foreach ($this->separators as $separator) {
                $line0_count = substr_count($line0, $separator);
                $line1_count = substr_count($line1, $separator);

                if ($line0_count === 0)
                    continue;

                if ($line0_count === $line1_count) {
                    if ($line0_count > $max_count)
                        $this->separator = $separator;
                }
            }
        }

        if ($this->separator === null)
            throw new \Exception('Cannot determine separator.');
    }

    private function getNextLine()
    {
        $this->line_Index++;

        if ($this->line_Index === 0)
            return $this->line0;
        if ($this->line_Index === 1)
            return $this->line1;
        
        return $this->getNextLine_Helper();
    }

    private function getNextLine_Helper()
    {
        $line = null;

        if ($this->file !== null) {
            $line = fgets($this->file);
            if ($line === false)
                return null;
            $line = str_replace("\r\n", "\n", $line);
            $line = str_replace("\n", "", $line);
        } else if ($this->textArr !== null) {
            if ($this->textArr_Current >= count($this->textArr))
                return null;

            $line = $this->textArr[$this->textArr_Current];

            $this->textArr_Current++;
        }

        if ($this->charset !== '')
            return EC\Strings\HEncoding::Convert($line, 'utf-8', $this->charset);

        return $line;
    }

    // private function readRow($line)
    // {
    //     $row = new CRow();

    //     $line = $this->readRow_ParseQuatations($line);

    //     $columns = explode($this->separator, $line);

    //     foreach ($columns as $column) {
    //         $column = str_replace('{{separator}}', $this->separator, $column);
    //         $row->addColumn($column);
    //     }

    //     return $row;
    // }

    // private function readRow_ParseQuatations($line)
    // {
    //     $quote = false;

    //     $regexp = "#(^|{$this->separator})\"(.*?)\"({$this->separator}|$)#s";

    //     preg_match_all($regexp, $line, $matches, PREG_SET_ORDER);

    //     foreach ($matches as $match) {
    //         $match_2 = str_replace($this->separator, '{{separator}}', $match[2]);

    //         $line = str_replace($match[0], $match[1] . $match_2 . $match[3],
    //                 $line);
    //     }

    //     return $line;
    // }

}