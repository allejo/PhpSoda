<?php

use allejo\Socrata\Exceptions\InvalidResourceException;
use allejo\Socrata\Exceptions\SodaException;
use allejo\Socrata\SodaClient;
use allejo\Socrata\SodaDataset;
use allejo\Socrata\SoqlQuery;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

class SodaDatasetTest extends TestCase
{
    /** @var SodaClient */
    private $client;

    private $id;
    private $domain;
    private $token;

    public function setUp()
    {
        $this->id     = 'pkfj-5jsd';
        $this->domain = 'opendata.socrata.com';
        $this->token  = 'khpKCi1wMz2bwXyMIHfb6ux73';

        $this->client = new SodaClient($this->domain, $this->token);
    }

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

     * @param string $resourceID The resource ID to be testing
     */
    public function testInvalidResourceID($resourceID)
    {
        $this->expectException(InvalidResourceException::class);

        new SodaDataset($this->client, $resourceID);
    }

    public function testGetDatasetWithInvalidCredentials()
    {
        $this->expectException(ClientException::class);

        $sc = new SodaClient($this->domain, $this->token, 'email@example.org', 'foobar');
        $ds = new SodaDataset($sc, 'pkfj-5jsd');

        $ds->getData();
    }

    public function testGetDatasetWithInvalidCredentialsExceptionCast()
    {
        try
        {
            $sc = new SodaClient($this->domain, $this->token, 'email@example.org', 'foobar');
            $ds = new SodaDataset($sc, 'pkfj-5jsd');

            $ds->getData();
        }
        catch (ClientException $e)
        {
            $cast = SodaException::cast($e);
            $this->assertEquals('authentication_required', $cast->getCode());
        }
    }

    public function testGetMetadataAsArray()
    {
        $ds = new SodaDataset($this->client, $this->id);
        $md = $ds->getMetadata();

        $this->assertNotNull($md);
        $this->assertEquals(1301955963, $md['createdAt']);
        $this->assertEquals('PUBLIC_DOMAIN', $md['licenseId']);
    }

    public function testGetMetadataAsObject()
    {
        $this->client->disableAssociativeArrays();

        $ds = new SodaDataset($this->client, $this->id);
        $md = $ds->getMetadata();

        $this->assertInstanceOf(stdClass::class, $md);
        $this->assertNotNull($md->createdAt);
    }

    public function testGetApiVersion()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $this->assertEquals(1, $ds->getApiVersion());
    }

    public function testGetResource()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $this->assertTrue(count($ds->getData()) > 5);
    }

    public function testGetResourceWithSoqlQuery()
    {
        $ds   = new SodaDataset($this->client, $this->id);
        $soql = new SoqlQuery();

        $soql->select('date_posted', 'state', 'sample_type')->where("state = 'AR'");

        $results = $ds->getData($soql);
        $this->assertTrue(count($results) > 1);
    }

    public function testGetIndividualRow()
    {
        $ds = new SodaDataset($this->client, $this->id);

        $this->assertCount(6, $ds->getRow(416));
    }

    public function testGetInvalidIndividualRow()
    {
        $this->expectException(ClientException::class);

        $ds = new SodaDataset($this->client, $this->id);
        $ds->getRow(1);
    }

    public function testGetInvalidIndividualRowExceptionCast()
    {
        try
        {
            $ds = new SodaDataset($this->client, $this->id);
            $ds->getRow(1);
        }
        catch (ClientException $e)
        {
            $cast = SodaException::cast($e);
            $this->assertEquals('row.missing', $cast->getCode());
        }
    }

    public function testGetDatasetWithSimpleFilter()
    {
        $simpleFilter = 'state=AR';
        $ds = new SodaDataset($this->client, $this->id);

        $results = $ds->getData($simpleFilter);

        foreach ($results as $result)
        {
            $this->assertEquals('AR', $result['state']);
        }
    }

    public function testGetDatasetWithArrayFilter()
    {
        $arrayFilter = ['state' => 'AZ'];
        $ds = new SodaDataset($this->client, $this->id);

        $results = $ds->getData($arrayFilter);

        foreach ($results as $result)
        {
            $this->assertEquals('AZ', $result['state']);
        }
    }
}
