<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlOrderDirection;
use allejo\Socrata\SoqlQuery;
use GuzzleHttp\Exception\ClientException;

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
        $this->id     = "pkfj-5jsd";
        $this->domain = "opendata.socrata.com";
        $this->token  = "khpKCi1wMz2bwXyMIHfb6ux73";

        $this->client  = new SodaClient($this->domain, $this->token);
        $this->dataset = new SodaDataset($this->client, $this->id);
    }

    public function testBadUrl ()
    {
        $this->expectException(ClientException::class);

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

    public static function invalidStringLimits ()
    {
        return array(
            array("7"),
            array("foo"),
            array("!")
        );
    }

    /**
     * @dataProvider invalidStringLimits
     */
    public function testStringLimitQuery ($limit)
    {
        $this->expectException(\InvalidArgumentException::class);

        $soql = new SoqlQuery();
        $soql->limit($limit);
    }

    public function testNegativeLimitQuery ()
    {
        $this->expectException(\OutOfBoundsException::class);

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

    public function testSelectColumnsQueryWithPartialAliases ()
    {
        $soql = new SoqlQuery();
        $soql->select(array("date_posted", "state", "sample_type" => "sample_value"));

        $this->assertEquals('$select=date_posted,state,sample_type%20AS%20sample_value', (string)$soql);

        $results = $this->dataset->getDataset($soql);

        $this->assertArrayHasKey("date_posted", $results[0]);
        $this->assertArrayHasKey("state", $results[0]);
        $this->assertArrayHasKey("sample_value", $results[0]);
        $this->assertArrayNotHasKey("sample_type", $results[0]);
    }

    public function testSelectColumnsQueryWithPartialAliasesWhereValueIsNull ()
    {
        $soql = new SoqlQuery();
        $soql->select(array("date_posted" => null, "state" => null, "sample_type" => "sample_value"));

        $this->assertEquals('$select=date_posted,state,sample_type%20AS%20sample_value', (string)$soql);

        $results = $this->dataset->getDataset($soql);

        $this->assertArrayHasKey("date_posted", $results[0]);
        $this->assertArrayHasKey("state", $results[0]);
        $this->assertArrayHasKey("sample_value", $results[0]);
        $this->assertArrayNotHasKey("sample_type", $results[0]);
    }

    public function testSelectColumnsQueryWithAliases ()
    {
        $soql = new SoqlQuery();
        $soql->select(array("date_posted" => "post_date", "state" => "current_state", "sample_type" => "sample_value"));

        $this->assertEquals('$select=date_posted%20AS%20post_date,state%20AS%20current_state,sample_type%20AS%20sample_value', (string)$soql);

        $results = $this->dataset->getDataset($soql);

        $this->assertArrayHasKey("post_date", $results[0]);
        $this->assertArrayNotHasKey("date_posted", $results[0]);
        $this->assertArrayHasKey("current_state", $results[0]);
        $this->assertArrayNotHasKey("state", $results[0]);
        $this->assertArrayHasKey("sample_value", $results[0]);
        $this->assertArrayNotHasKey("sample_type", $results[0]);
    }

    public function testForceByteOrderMarkTrue ()
    {
        $soql = new SoqlQuery();
        $soql->forceByteOrderMark();

        $this->assertEquals('$$bom=true', (string)$soql);
    }

    public function testForceByteOrderMarkFalse ()
    {
        $soql = new SoqlQuery();
        $soql->forceByteOrderMark(false);

        $this->assertEquals('$$bom=false', (string)$soql);
    }

    public function testQueryQuery ()
    {
        $query = 'SELECT dataset_column, post_date WHERE count > 5';

        $soql = new SoqlQuery();
        $soql->query($query);

        $this->assertEquals(sprintf('$query=%s', rawurlencode($query)), (string)$soql);
    }
}
