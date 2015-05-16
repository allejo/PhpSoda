<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlQuery;

class SoqlQueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SodaClient
     */
    private $client;

    /**
     * @var SodaDataset
     */
    private $dataset;

    private $id;
    private $domain;
    private $token;

    public function setUp ()
    {
        $this->id = "pkfj-5jsd";
        $this->domain = "opendata.socrata.com";
        $this->token = "khpKCi1wMz2bwXyMIHfb6ux73";

        $this->client = new SodaClient($this->domain, $this->token);
        $this->dataset = new SodaDataset($this->client, $this->id);
    }

    public function testSelectColumnsWithNoParamQuery ()
    {
        $soql_one = new SoqlQuery();
        $soql_one->select();

        $soql_two = new SoqlQuery();

        $this->assertEquals($soql_one, $soql_two);
    }

    public function testSelectColumnsWithArrayAsParamQuery ()
    {
        $soql_one = new SoqlQuery();
        $soql_one->select("date_posted", "state", "sample_type");

        $soql_two = new SoqlQuery();
        $soql_two->select(array("date_posted", "state", "sample_type"));

        $this->assertEquals($soql_one, $soql_two);
    }

    public function testSelectColumnsQuery ()
    {
        $soql = new SoqlQuery();
        $soql->select("date_posted", "state", "sample_type");

        $results = $this->dataset->getDataset($soql);

        $this->assertArrayHasKey("date_posted", $results[0]);
        $this->assertArrayHasKey("state", $results[0]);
        $this->assertArrayHasKey("sample_type", $results[0]);
        $this->assertArrayNotHasKey("foo", $results[0]);
    }

    public function testWhereQuery ()
    {
        $soql = new SoqlQuery();
        $soql->where("state = 'AR'");

        $results = $this->dataset->getDataset($soql);

        foreach ($results as $result)
        {
            $this->assertEquals($result['state'], 'AR');
        }
    }

    public function testLimitQuery ()
    {
        $limit = 7;

        $soql = new SoqlQuery();
        $soql->limit($limit);

        $results = $this->dataset->getDataset($soql);

        $this->assertEquals($limit, count($results));
    }
}