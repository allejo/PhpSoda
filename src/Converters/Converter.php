<?php

/**
 * This file contains the base class for converters which PhpSoda will support
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Converters;

use allejo\Socrata\Exceptions\FileNotFoundException;

/**
 * The base class to support custom data formats other than JSON; this is so PhpSoda can support for your any data
 * format.
 *
 * TosSupport custom data types other than JSON, you must provide a conversion method from your data format to JSON.
 * This base class provides a method to create an instance of itself from a file and it defines the abstract method
 * `toJson()` that must be implemented in your converter.
 *
 * As an example, PhpSoda's official support of CSV is a converter extended from this class and implements a conversion
 * from CSV to JSON.
 *
 * @package allejo\Socrata\Converters
 * @since   0.1.0
 */
abstract class Converter
{
    /**
     * The data to be converted into the custom data format. For example, in the CSV converter, this variable stores
     * the CSV formatted data.
     *
     * @var string
     */
    protected $data;

    /**
     * Create a converter that will convert data from your data format into JSON
     *
     * @param string $customFormattedData The data (in your custom data format) to be converted into JSON
     *
     * @since 0.1.0
     */
    public function __construct ($customFormattedData)
    {
        $this->data = $customFormattedData;
    }

    /**
     * A convenience method to create a Converter instance from a file name without having to read the file data and
     * then give it to the CsvConverter constructor.
     *
     * @param  string $filename The path or filename of the CSV file to open and create a CsvConverter for
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

    /**
     * Convert the current data stored into a JSON formatted string to be submitted to Socrata
     *
     * @return string A JSON formatted string
     */
    abstract public function toJson ();
}
