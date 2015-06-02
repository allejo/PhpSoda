<?php

use allejo\Socrata\Converters\CsvConverter;

class SodaCsvConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \allejo\Socrata\Exceptions\FileNotFoundException
     */
    public function testInvalidCsvFile ()
    {
        $csv = CsvConverter::fromFile("path/to/fake-file.csv");
    }

    public function testCsvFromFile ()
    {
        $csvFile = file_get_contents("tests/datasets/dataset.csv");
        $csvConverter = new CsvConverter($csvFile);

        $csvFromHelper = CsvConverter::fromFile("tests/datasets/dataset.csv");

        $this->assertEquals($csvConverter->toJson(), $csvFromHelper->toJson());
    }
}
