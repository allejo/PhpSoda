<?php

use allejo\Socrata\SoqlOrderDirection;

class SoqlOrderDirectionTest extends PHPUnit_Framework_TestCase
{
    public static function invalidParseOrder ()
    {
        return array(
            array("ascending"),
            array("descending"),
            array("alphabetical"),
        );
    }

    public static function validParseOrder ()
    {
        return array(
            array("ASC"),
            array("DESC")
        );
    }

    /**
     * @dataProvider invalidParseOrder
     * @expectedException \InvalidArgumentException
     *
     * @param  string  $order  The sort order to be tested
     */
    public function testInvalidParseOrder ($order)
    {
        SoqlOrderDirection::parseOrder($order);
    }

    /**
     * @dataProvider validParseOrder
     *
     * @param  string  $order  The sort order to be tested
     */
    public function testValidParseOrder ($order)
    {
        $this->assertEquals($order, SoqlOrderDirection::parseOrder($order));
    }
}
