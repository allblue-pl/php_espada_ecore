<?php namespace EC\CSV;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CCSV
{

    private $separators = null;
    private $charset = '';

    private $separator = '';

    private $file = null;
    private $rowIndex = 0;
    private $line0 = null;
    private $line1 = null;

    public function __construct($separators = [';', ','], $charset = '')
    {
        $this->separators = $separators;
        $this->charset = $charset;
    }

    public function close()
    {
        if ($this->file !== null)
            fclose($this->file);
        $this->file = null;

        $this->rowIndex = 0;

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

        $this->line0 = $this->nextLine();
        if ($this->line0 === null)
            return true;

        $this->line1 = $this->nextLine();

        $this->determineSeparator();

        return true;
    }

    public function nextRow()
    {
        $line = null;
        $row = null;
        if ($this->rowIndex === 0)
            $line = $this->line0;
        else if ($this->rowIndex === 1)
            $line = $this->line1;
        else
            $line = $this->nextLine();

        if ($line === null)
            return null;

        $row = $this->readRow($line);

        $this->rowIndex++;

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

    private function nextLine()
    {
        $line = fgets($this->file);
        if ($line === false)
            return null;
        if (preg_match("#(^|{$this->separator})\"(.*?)$#", $line)) {
            $line_part = fgets($this->file);
            if ($line_part !== null)
                $line .= $line_part;
        }

        if ($this->charset !== '')
            return EC\Strings\HEncoding::Convert($line, 'utf-8', $this->charset);

        return $line;
    }

    private function readRow($line)
    {
        $row = new CRow();

        $line = $this->readRow_ParseQuatations($line);

        $columns = explode($this->separator, $line);

        foreach ($columns as $column) {
            $column = str_replace('{{separator}}', $this->separator, $column);
            $row->addColumn($column);
        }

        return $row;
    }

    private function readRow_ParseQuatations($line)
    {
        $regexp = "#(^|{$this->separator})'(.*?)\"({$this->separator}|$)#s";

        preg_match_all($regexp, $line, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $match_2 = str_replace($this->separator, '{{separator}}', $match[2]);

            $line = str_replace($match[0], $match[1] . $match_2 . $match[3],
                    $line);
        }

        return $line;
    }

}
