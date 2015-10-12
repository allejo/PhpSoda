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
        $this->id     = "5anq-ef2c";
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

        $json = file_get_contents("tests/datasets/dataset.json");

        $ds->upsert($json);
    }

    public function testUpsertArray ()
    {
        $array = array(
            array("name" => "Foo Bar", "type" => "Australian"),
            array("name" => "Qux Baz", "type" => "Book Keeper"),
            array("name" => "Bon Qaz", "type" => "Telemarketer")
        );

        $ds = new SodaDataset($this->client, $this->id);
        $ds->upsert($array);
    }

    public function testUpsertCsv ()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $csvFile = file_get_contents("tests/datasets/dataset.csv");
        $csv = new CsvConverter($csvFile);

        $ds->upsert($csv);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUpsertInvalidData ()
    {
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

        $soql->select(":id,name,type");

        $result = $ds->getDataset($soql);

        $ds->deleteRow($result[0][":id"]);

        $this->assertCount(2, $ds->getDataset());
    }
}
