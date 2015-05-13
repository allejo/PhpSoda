<?php

use allejo\Socrata\SodaClient;

class SodaClientTest extends PHPUnit_Framework_TestCase
{
    public static function invalidResourceIDs()
    {
        return array(
            array("pkfj5jsd"),
            array("pk#j-5j!d"),
            array("1234-werwe"),
            array("123--4545")
        );
    }

    /**
     * @dataProvider invalidResourceIDs
     * @expectedException \allejo\Socrata\Exceptions\InvalidResourceException
     *
     * @param $resourceID string The resource ID to be testing
     *
     * @throws \allejo\Socrata\Exceptions\InvalidResourceException
     */
    public function testInvalidResourceIDs($resourceID)
    {
        $sc = new SodaClient("https://opendata.socrata.com");
        $sc->getResource($resourceID);
    }

    /**
     * @expectedException \allejo\Socrata\Exceptions\HttpException
     * @expectedExceptionCode 403
     */
    public function testGetResourceWithoutToken()
    {
        $sc = new SodaClient("opendata.socrata.com");
        $sc->getResource("pkfj-5jsd");
    }
}