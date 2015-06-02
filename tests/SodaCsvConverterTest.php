<?php

use allejo\Socrata\Converters\CsvConverter;

class SodaCsvConverterTest extends PHPUnit_Framework_TestCase
{
    public function testCsvFromFile ()
    {
        $csvFile = file_get_contents("tests/datasets/dataset.csv");
        $csvConverter = new CsvConverter($csvFile);

        $csvFromHelper = CsvConverter::fromFile("tests/datasets/dataset.csv");

        $this->assertEquals($csvConverter->toJson(), $csvFromHelper->toJson());
    }
}
