<?php

use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\Converters\CsvConverter;
use allejo\Socrata\SoqlQuery;

class SodaDatasetAuthenticatedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SodaClient
     */
    private $client;

    private $id;
    private $domain;
    private $token;

    public function setUp ()
    {
        $this->id     = "pwsy-nn4t";
        $this->domain = "opendata.socrata.com";
        $this->token  = "khpKCi1wMz2bwXyMIHfb6ux73";

        $authClient = new \TestsAuthentication("phpunit-auth.json");

        if (!$authClient->isAuthenticationSetup())
        {
            $this->markTestSkipped();
        }

        $this->client = new SodaClient($this->domain, $this->token, $authClient->getUsername(), $authClient->getPassword());
    }

    public function testUpsertJson ()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $json = file_get_contents(__DIR__ . "/datasets/dataset.json");

        $ds->upsert($json);
    }

    public function testUpsertArray ()
    {
        $array = array(
            array("date" => "2016-01-01", "os" => "Macintosh", "visits" => 10),
            array("date" => "2016-01-01", "os" => "Windows", "visits" => 20),
            array("date" => "2016-01-01", "os" => "iOS", "visits" => 30)
        );

        $ds = new SodaDataset($this->client, $this->id);
        $ds->upsert($array);
    }

    public function testUpsertCsv ()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $csvFile = file_get_contents(__DIR__ . "/datasets/dataset.csv");
        $csv = new CsvConverter($csvFile);

        $ds->upsert($csv);
    }

    public function testUpsertInvalidData ()
    {
        $this->expectException(\InvalidArgumentException::class);

        $ds = new SodaDataset($this->client, $this->id);

        $ds->upsert("muffin and buttons");
    }

    public function testReplaceJson ()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $json = file_get_contents("tests/datasets/dataset.json");

        $ds->replace($json);
    }

    public function testDeleteRow ()
    {
        $ds   = new SodaDataset($this->client, $this->id);
        $soql = new SoqlQuery();

        $soql->select(":id,date,os,visits");

        $result = $ds->getDataset($soql);

        $uID = $result[0][':id'];

        $ds->deleteRow($uID);

        $soql->select(':id')
             ->where(':id = ' . $uID);

        $new_result = $ds->getDataset($soql);

        $this->assertEmpty($new_result);
    }
}
