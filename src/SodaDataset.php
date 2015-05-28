<?php

namespace allejo\Socrata;

use allejo\Socrata\Converters\Converter;
use allejo\Socrata\Utilities\StringUtilities;
use allejo\Socrata\Utilities\UrlQuery;

class SodaDataset
{
    private $sodaClient;
    private $urlQuery;
    private $resourceId;
    private $apiVersion;

    public function __construct ($sodaClient, $resourceID)
    {
        StringUtilities::validateResourceID($resourceID);

        if (!($sodaClient instanceof SodaClient))
        {
            throw new \InvalidArgumentException("The first variable is expected to be a SodaClient object");
        }

        $this->apiVersion = 0;
        $this->sodaClient = $sodaClient;
        $this->resourceId = $resourceID;
        $this->urlQuery   = new UrlQuery($this->buildResourceUrl(), $this->sodaClient->getToken(), $this->sodaClient->getEmail(), $this->sodaClient->getPassword());
    }

    /**
     * Get the API version this dataset is using
     *
     * @return int The API version number
     */
    public function getApiVersion ()
    {
        // If we don't have the API version set, send a dummy query with limit 0 since we only care about the headers
        if ($this->apiVersion == 0)
        {
            $soql = new SoqlQuery();
            $soql->limit(0);

            // When we fetch a dataset, the API version is stored
            $this->getDataset($soql);
        }

        return $this->apiVersion;
    }

    /**
     * Get the metadata of a dataset
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     *
     * @since  0.1.0
     *
     * @return array The metadata as a PHP array. The array will contain associative arrays or stdClass objects from
     *               the decoded JSON received from the data set.
     */
    public function getMetadata ()
    {
        $metadataUrlQuery = new UrlQuery($this->buildViewUrl(), $this->sodaClient->getToken(), $this->sodaClient->getEmail(), $this->sodaClient->getPassword());

        return $metadataUrlQuery->sendGet("", $this->sodaClient->associativeArrayEnabled());
    }

    /**
     * Fetch a dataset based on a resource ID.
     *
     * @param  string|SoqlQuery $filterOrSoqlQuery A simple filter or a SoqlQuery to filter the results
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     *
     * @since  0.1.0
     *
     * @return array The data set as a PHP array. The array will contain associative arrays or stdClass objects from
     *               the decoded JSON received from the data set.
     */
    public function getDataset ($filterOrSoqlQuery = "")
    {
        $headers = array();

        if (!($filterOrSoqlQuery instanceof SoqlQuery) && StringUtilities::isNullOrEmpty($filterOrSoqlQuery))
        {
            $filterOrSoqlQuery = new SoqlQuery();
        }

        $dataset = $this->urlQuery->sendGet($filterOrSoqlQuery, $this->sodaClient->associativeArrayEnabled(), $headers);

        // Only set the API version number if it hasn't been set yet
        if ($this->apiVersion == 0)
        {
            $this->apiVersion = self::parseApiVersion($headers);
        }

        return $dataset;
    }

    /**
     * Create, update, and delete rows in a single operation, using their row identifiers.
     *
     * Data will always be transmitted as JSON to Socrata even though different forms are accepted. In order to pass
     * other forms of data, you must use a Converter class that has a `toJson()` method, such as the CsvConverter.
     *
     * @param  array|Converter|JSON $data  The data that will be upserted to the Socrata dataset as a PHP array, an
     *                                     instance of a Converter child class, or a JSON string
     *
     * @link   http://dev.socrata.com/publishers/upsert.html Updating Rows in Bulk with Upsert
     *
     * @see    Converter
     * @see    CsvConverter
     *
     * @since  0.1.0
     *
     * @return mixed
     */
    public function upsert ($data)
    {
        $upsertData = $data;

        if (is_array($data))
        {
            $upsertData = json_encode($data);
        }
        else if ($data instanceof Converter)
        {
            $upsertData = $data->toJson();
        }
        else if (!StringUtilities::isJson($data))
        {
            throw new \InvalidArgumentException("The given data is not valid JSON");
        }

        return $this->urlQuery->sendPost($upsertData, $this->sodaClient->associativeArrayEnabled());
    }

    /**
     * Build the API URL that will be used to access the dataset
     *
     * @return string The apt API URL
     */
    private function buildResourceUrl ()
    {
        return $this->buildApiUrl("resource");
    }

    /**
     * Build the API URL that will be used to access the metadata for the dataset
     *
     * @return string The apt API URL
     */
    private function buildViewUrl ()
    {
        return $this->buildApiUrl("views");
    }

    /**
     * Build the URL that will be used to access the API for the respective action
     *
     * @param  string $location The location of where to get information from
     *
     * @return string The API URL
     */
    private function buildApiUrl ($location)
    {
        return sprintf("%s://%s/%s/%s.json", UrlQuery::DEFAULT_PROTOCOL, $this->sodaClient->getDomain(), $location, $this->resourceId);
    }

    /**
     * Determine the version number of the API this dataset is using
     *
     * @param  array  $responseHeaders  An array with the cURL headers received
     *
     * @return int    The Socrata API version number this dataset uses
     */
    private static function parseApiVersion ($responseHeaders)
    {
        // A header that's unique to the legacy API
        if (array_key_exists('X-SODA2-Legacy-Types', $responseHeaders) && $responseHeaders['X-SODA2-Legacy-Types'])
        {
            return 1;
        }

        // A header that's unique to the new API
        if (array_key_exists('X-SODA2-Truth-Last-Modified', $responseHeaders))
        {
            return 2;
        }

        return 0;
    }
}
