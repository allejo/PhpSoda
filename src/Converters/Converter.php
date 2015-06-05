<?php

namespace allejo\Socrata\Converters;

use allejo\Socrata\Exceptions\FileNotFoundException;

abstract class Converter
{
    protected $data;

    public function __construct ($formattedString)
    {
        $this->data = $formattedString;
    }

    abstract public function toJson ();

    /**
     * A convenience method to create a Converter instance from a file name without having to read the file data and
     * then give it to the CsvConverter constructor.
     *
     * @param  string  $filename  The path or filename of the CSV file to open and create a CsvConverter for
     *
     * @throws \allejo\Socrata\Exceptions\FileNotFoundException
     *
     * @return static
     */
    public static function fromFile ($filename)
    {
        if (!file_exists($filename) || !is_readable($filename))
        {
            throw new FileNotFoundException($filename);
        }

        $data = file_get_contents($filename);

        return new static($data);
    }
}
