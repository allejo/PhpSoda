<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlQuery;

class SodaClientTest extends PHPUnit_Framework_TestCase
{
    private $id;
    private $domain;
    private $token;

    public static function invalidResourceIDs()
    {
        return array(
            array("pkfj5jsd"),
            array("pk#j-5j!d"),
            array("1234-werwe"),
            array("123--4545")
        );
    }

    public function setUp ()
    {
        $this->id = "pkfj-5jsd";
        $this->domain = "opendata.socrata.com";
        $this->token = "khpKCi1wMz2bwXyMIHfb6ux73";
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
        $ds = new SodaDataset($sc, $resourceID);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidClient()
    {
        $sc = null;
        $ds = new SodaDataset($sc, "qwer-1234");
    }

    /**
     * @expectedException \allejo\Socrata\Exceptions\HttpException
     * @expectedExceptionCode 403
     */
    public function testGetDatasetWithInvalidCredentials()
    {
        $sc = new SodaClient($this->domain, $this->token, "fake@email.com", "foobar");
        $ds = new SodaDataset($sc, "pkfj-5jsd");

        $ds->getDataset();
    }

    public function testGetResourceWithToken()
    {
        $sc = new SodaClient($this->domain, $this->token);
        $ds = new SodaDataset($sc, "pkfj-5jsd");

        $ds->getDataset();
    }

    public function testGetResourceWithSoqlQuery()
    {
        $sc   = new SodaClient($this->domain, $this->token);
        $ds   = new SodaDataset($sc, $this->id);
        $soql = (new SoqlQuery())
                ->select(array("date_posted", "state", "sample_type"))
                ->where("state = 'AR'");

        $results = $ds->getDataset($soql);
        $this->assertEquals(2, count($results));
    }
}