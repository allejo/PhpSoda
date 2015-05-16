<?php

use allejo\Socrata\Utilities\StringUtilities;

class StringUtilitiesTest extends PHPUnit_Framework_TestCase
{
    public static function invalidJSON()
    {
        return array(
            array('[{foo": "bar"}]'),
            array('foo bar'),
            array(null),
        );
    }

    public static function invalidStrings()
    {
        return array(
            array(""),
            array(" "),
            array("  "),
            array(null)
        );
    }

    public static function validJSON()
    {
        return array(
            array('[{"foo": "bar"}]'),
            array(json_encode(array("foo" => "bar")))
        );
    }

    public static function validStrings()
    {
        return array(
            array("foo"),
            array("_"),
            array(" bar")
        );
    }

    /**
     * @dataProvider SodaDatasetTest::invalidResourceIDs
     * @expectedException allejo\Socrata\Exceptions\InvalidResourceException
     *
     * @param $resourceID string The resource ID to be testing
     */
    public function testValidateResourceID($resourceID)
    {
        StringUtilities::validateResourceID($resourceID);
    }

    /**
     * @dataProvider validJSON
     *
     * @param  string  $data  The string being tested to check if it's null or whitespace
     */
    public function testIsJsonWithValidJSON($data)
    {
        $this->assertTrue(StringUtilities::isJson($data));
    }

    /**
     * @dataProvider invalidJSON
     *
     * @param  string  $data  The string being tested to check if it's null or whitespace
     */
    public function testIsJsonWithInvalidJSON($data)
    {
        $this->assertFalse(StringUtilities::isJson($data));
    }

    /**
     * @dataProvider invalidStrings
     *
     * @param  string  $string  The string being tested to check if it's null or whitespace
     */
    public function testIsNullOfEmptyWithInvalidStrings($string)
    {
        $this->assertTrue(StringUtilities::isNullOrEmpty($string));
    }

    /**
     * @dataProvider validStrings
     *
     * @param  string  $string  The string being tested to check if it's null or whitespace
     */
    public function testIsNullOfEmptyWithValidStrings($string)
    {
        $this->assertFalse(StringUtilities::isNullOrEmpty($string));
    }
}
