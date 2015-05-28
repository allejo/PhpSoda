<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlOrderDirection;
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

    public function invalidStringLimits ()
    {
        return array(
            array("7"),
            array("foo"),
            array("!")
        );
    }

    public function setUp ()
    {
        $this->id     = "pkfj-5jsd";
        $this->domain = "opendata.socrata.com";
        $this->token  = "khpKCi1wMz2bwXyMIHfb6ux73";

        $this->client  = new SodaClient($this->domain, $this->token);
        $this->dataset = new SodaDataset($this->client, $this->id);
    }

    /**
     * @expectedException \allejo\Socrata\Exceptions\HttpException
     */
    public function testBadUrl ()
    {
        $client  = new SodaClient("www.example.com");
        $dataset = new SodaDataset($client, "qwer-trew");

        $dataset->getDataset();
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

    /**
     * @dataProvider invalidStringLimits
     * @expectedException \InvalidArgumentException
     */
    public function testStringLimitQuery ($limit)
    {
        $soql = new SoqlQuery();
        $soql->limit($limit);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testNegativeLimitQuery ()
    {
        $limit = -10;

        $soql = new SoqlQuery();
        $soql->limit($limit);
    }

    public function testOffsetQuery ()
    {
        $offset = 5;

        $soql = new SoqlQuery();
        $soql->offset($offset);

        $normal_results = $this->dataset->getDataset();
        $offset_results = $this->dataset->getDataset($soql);

        for ($i = 0; $i < 5; $i++)
        {
            $this->assertEquals($normal_results[$offset + $i], $offset_results[$i]);
        }
    }

    public function testOrderAscQuery ()
    {
        $soql = new SoqlQuery();
        $soql->order("state", SoqlOrderDirection::ASC)
             ->limit(5);

        $results = $this->dataset->getDataset($soql);
        $loop_iterations = count($results) - 1;

        for ($i = 0; $i < $loop_iterations; $i++)
        {
            $this->assertLessThanOrEqual($results[$i + 1]['state'], $results[$i]['state']);
        }
    }

    public function testOrderDescQuery ()
    {
        $soql = new SoqlQuery();
        $soql->order("state", SoqlOrderDirection::DESC)
             ->limit(5);

        $results = $this->dataset->getDataset($soql);
        $loop_iterations = count($results) - 1;

        for ($i = 0; $i < $loop_iterations; $i++)
        {
            $this->assertGreaterThanOrEqual($results[$i + 1]['state'], $results[$i]['state']);
        }
    }

    /**
     * @expectedException allejo\Socrata\Exceptions\SodaException
     */
    public function testMultipleOrderQueryWithApiV1 ()
    {
        $soql = new SoqlQuery();
        $soql->order("state", SoqlOrderDirection::DESC)
             ->order("date_posted", SoqlOrderDirection::ASC)
             ->limit(5);

        $this->dataset->getDataset($soql);
    }

    public function testMultipleSelectsQuery ()
    {
        $soql = new SoqlQuery();
        $soql->select("first", "second")
             ->select("third");

        $expected = '$select=third';

        $this->assertContains($expected, (string)$soql);
    }

    public function testGroupingQuery ()
    {
        $columnToGroup = "myGroup";

        $soql = new SoqlQuery();
        $soql->group($columnToGroup);

        $expected = '$group=' . $columnToGroup;

        $this->assertContains($expected, (string)$soql);
    }

    public function testMultipleGroupingQuery ()
    {
        $firstColumnToGroup = "1stGroup";
        $secondColumnToGroup = "2ndGroup";

        $soql = new SoqlQuery();
        $soql->group($firstColumnToGroup)
             ->group($secondColumnToGroup);

        $expected = '$group=' . implode(',', array($firstColumnToGroup, $secondColumnToGroup));

        $this->assertContains($expected, (string)$soql);
    }
}
