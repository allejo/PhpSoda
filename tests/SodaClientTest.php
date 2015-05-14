<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SoqlQuery;

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
    public function testGetResourceWithoutTokenAndInvalidCredentials()
    {
        $sc = new SodaClient("opendata.socrata.com", "", "fake@email.com", "foobar");
        $sc->getResource("pkfj-5jsd");
    }

    public function testGetResourceWithToken()
    {
        $sc = new SodaClient("opendata.socrata.com", "khpKCi1wMz2bwXyMIHfb6ux73");
        $sc->getResource("pkfj-5jsd");
    }

    public function testGetResourceWithSoqlQuery()
    {
        $sc = new SodaClient("opendata.socrata.com", "khpKCi1wMz2bwXyMIHfb6ux73");
        $soql = new SoqlQuery();

        $soql->select(array("date_posted", "state", "sample_type"))
             ->where("state = 'AR'");

        $results = $sc->getResource("pkfj-5jsd", $soql);

        print_r($results);
    }
}