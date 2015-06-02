<?php

namespace allejo\Socrata\Converters;

use allejo\Socrata\Exceptions\FileNotFoundException;

class CsvConverter extends Converter
{
    private $data;

    public function __construct ($csv)
    {
        $this->data = $csv;
    }

    /**
     * Convert the data that was given to this object into JSON.
     *
     * @link   http://steindom.com/articles/shortest-php-code-convert-csv-associative-array
     *
     * @return string A JSON encoded string
     */
    public function toJson ()
    {
        $rows = array_map("str_getcsv", explode("\n", trim($this->data)));
        $columns = array_shift($rows);
        $csv = array();

        foreach ($rows as $row)
        {
            $csv[] = array_combine($columns, $row);
        }

        return json_encode($csv);
    }

    /**
     * A convenience method to create a CsvConverter instance from a file name without having to read the file data and
     * then give it to the CsvConverter constructor.
     *
     * @param  string  $filename  The path or filename of the CSV file to open and create a CsvConverter for
     *
     * @throws \allejo\Socrata\Exceptions\FileNotFoundException
     *
     * @return \allejo\Socrata\Converters\CsvConverter
     */
    public static function fromFile ($filename)
    {
        if (!file_exists($filename) || !is_readable($filename))
        {
            throw new FileNotFoundException($filename);
        }

        $data = file_get_contents($filename);

        return new self($data);
    }
}
