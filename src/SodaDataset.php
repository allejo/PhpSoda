<?php

/**
 * This file contains the SodaDataset class
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata;

use allejo\Socrata\Converters\Converter;
use allejo\Socrata\Exceptions\InvalidResourceException;
use allejo\Socrata\Utilities\StringUtilities;
use allejo\Socrata\Utilities\UrlQuery;
use GuzzleHttp\Exception\ClientException;

/**
 * An object provided to interact with a Socrata dataset directly. Provides functionality for fetching the dataset, an
 * individual row, or updating/replacing a dataset.
 *
 * @api
 * @since 0.1.0
 */
class SodaDataset
{
    /** @var SodaClient */
    private $sodaClient;

    /**
     * The object used to make URL jobs for common requests
     *
     * @deprecated
     * @var UrlQuery
     */
    private $urlQuery;

    /** @var string */
    private $resourceId;

    /** @var double */
    private $apiVersion;

    /** @var array */
    private $metadata;

    /**
     * Create an object for interacting with a Socrata dataset
     *
     * @param  SodaClient $sodaClient The SodaClient with all of the authentication information and settings for access
     * @param  string     $resourceID The 4x4 resource ID of the dataset that will be referenced
     *
     * @throws InvalidResourceException If the given resource ID does not match the pattern of a resource ID
     *
     * @since 0.1.0
     */
    public function __construct (SodaClient $sodaClient, $resourceID)
    {
        StringUtilities::validateResourceID($resourceID);

        $this->apiVersion = 0;
        $this->sodaClient = $sodaClient;
        $this->resourceId = $resourceID;
        $this->urlQuery   = new UrlQuery($this->buildResourceUrl(), $this->sodaClient->getToken(), $this->sodaClient->getEmail(), $this->sodaClient->getPassword());
    }

    //
    // Getters
    //

    /**
     * Get the API version this dataset is using
     *
     * @api
     *
     * @since  0.1.0
     *
     * @throws ClientException
     *
     * @return double The API version number
     */
    public function getApiVersion ()
    {
        // If we don't have the API version set, send a dummy query with limit 0 since we only care about the headers and
        // Socrata doesn't accept HEAD requests.
        if ($this->apiVersion == 0)
        {
            $soql = new SoqlQuery();
            $soql->limit(0);

            // When we fetch a dataset, the API version is stored
            $this->getData($soql);
        }

        return $this->apiVersion;
    }

    /**
     * Get the metadata of a dataset.
     *
     * @api
     *
     * @param  bool $forceFetch Set to true if the cached metadata for the dataset is outdated or needs to be refreshed
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     *
     * @since  0.1.0
     *
     * @throws ClientException
     *
     * @return array|\stdClass The metadata of the dataset
     */
    public function getMetadata($forceFetch = false)
    {
        if (empty($this->metadata) || $forceFetch)
        {
            $response = $this->sodaClient->getGuzzleClient()->get(sprintf('views/%s.json', $this->resourceId));
            $this->metadata = json_decode(
                $response->getBody()->getContents(),
                $this->sodaClient->associativeArrayEnabled()
            );
        }

        return $this->metadata;
    }

    /**
     * Get the column structure of the dataset.
     *
     * @api
     *
     * @param  bool $forceFetch Set to true if the cached metadata for the dataset is outdated or needs to be refreshed
     *
     * @since  2.0.0
     *
     * @throws ClientException
     *
     * @return array
     */
    public function getColumns($forceFetch = false)
    {
        $metadata = $this->getMetadata($forceFetch);

        if ($metadata instanceof \stdClass)
        {
            return $metadata->columns;
        }

        return $metadata['columns'];
    }

    /**
     * Fetch the data belonging to this dataset.
     *
     * @api
     *
     * @param  array|string|SoqlQuery $filterOrSoqlQuery A simple filter or a SoqlQuery to filter the results
     * @param  array                  $headers           A reference to an array where headers from the response can be stored
     *
     * @since  2.0.0 Renamed to getData() and introduces new $headers parameter
     * @since  0.1.0
     *
     * @throws ClientException
     *
     * @return array
     */
    public function getData($filterOrSoqlQuery = '', array &$headers = null)
    {
        if ($filterOrSoqlQuery instanceof SoqlQuery)
        {
            $filterOrSoqlQuery = (string)$filterOrSoqlQuery;
        }

        $response = $this->sodaClient->getGuzzleClient()->get(sprintf('resource/%s.json', $this->resourceId), [
            'query' => $filterOrSoqlQuery
        ]);

        if ($headers !== null)
        {
            $headers = $response->getHeaders();
        }

        $this->setApiVersion($response->getHeaders());

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetch the data belonging to this dataset.
     *
     * @api
     *
     * @deprecated 1.0.2 Renamed to SodaDataset::getData() in 2.0.0
     *
     * @param  string|SoqlQuery $filterOrSoqlQuery A simple filter or a SoqlQuery to filter the results
     *
     * @since  0.1.0
     *
     * @return array
     */
    public function getDataset($filterOrSoqlQuery = '')
    {
        return $this->getData($filterOrSoqlQuery);
    }

    /**
     * Fetch an individual row from a dataset.
     *
     * @param  int|string $rowID The row identifier of the row to fetch; if no identifier is set for the dataset, the
     *                           internal row identifier should be used
     *
     * @link   https://dev.socrata.com/publishers/direct-row-manipulation.html#retrieving-an-individual-row  Retrieving An Individual Row
     *
     * @since  0.1.2
     *
     * @throws ClientException
     *
     * @return array|\stdClass An individual row
     */
    public function getRow($rowID)
    {
        $response = $this->sodaClient->getGuzzleClient()->get(sprintf('resource/%s/%s.json', $this->resourceId, $rowID));

        $this->setApiVersion($response->getHeaders());

        return json_decode($response->getBody()->getContents(), $this->sodaClient->associativeArrayEnabled());
    }

    //
    // Editors
    //

    /**
     * Delete an individual row based on their row identifier. For deleting more than a single row, use an upsert
     * instead.
     *
     * @param  int|string $rowID The row identifier of the row to fetch; if no identifier is set for the dataset, the
     *                           internal row identifier should be used
     *
     * @link   http://dev.socrata.com/publishers/direct-row-manipulation.html#deleting-a-row Deleting a Row
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     * @see    upsert()
     *
     * @since  0.1.2
     *
     * @return mixed An object with information about the deletion. The array will contain associative arrays or
     *               stdClass objects from the decoded JSON received from the data set.
     */
    public function deleteRow ($rowID)
    {
        return $this->individualRow($rowID, "delete");
    }

    /**
     * Replace the entire dataset with the new payload provided
     *
     * Data will always be transmitted as JSON to Socrata even though different forms are accepted. In order to pass
     * other forms of data, you must use a Converter class that has a `toJson()` method, such as the CsvConverter.
     *
     * @param  array|Converter|JSON $payload  The data that will be upserted to the Socrata dataset as a PHP array, an
     *                                        instance of a Converter child class, or a JSON string
     *
     * @link   http://dev.socrata.com/publishers/replace.html Replacing a dataset with Replace
     *
     * @see    Converter
     * @see    CsvConverter
     *
     * @since  0.1.0
     *
     * @return mixed
     */
    public function replace ($payload)
    {
        $upsertData = $this->handleJson($payload);

        return $this->urlQuery->sendPut($upsertData, $this->sodaClient->associativeArrayEnabled());
    }

    /**
     * Create, update, and delete rows in a single operation, using their row identifiers.
     *
     * Data will always be transmitted as JSON to Socrata even though different forms are accepted. In order to pass
     * other forms of data, you must use a Converter class that has a `toJson()` method, such as the CsvConverter.
     *
     * @param  array|Converter|JSON $payload  The data that will be upserted to the Socrata dataset as a PHP array, an
     *                                        instance of a Converter child class, or a JSON string
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
    public function upsert ($payload)
    {
        $upsertData = $this->handleJson($payload);

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
     * @param  string      $location    The location of where to get information from
     * @param  string|null $identifier  The part of the URL that will end with .json. This will either be the resource
     *                                  ID or it will be a row ID prepended with the resource ID
     *
     * @return string The API URL
     */
    private function buildApiUrl ($location, $identifier = NULL)
    {
        if ($identifier === NULL)
        {
            $identifier = $this->resourceId;
        }

        return sprintf("%s://%s/%s/%s.json", UrlQuery::DEFAULT_PROTOCOL, $this->sodaClient->getDomain(), $location, $identifier);
    }

    /**
     * Handle different forms of data to be returned in JSON format so it can be sent to Socrata.
     *
     * Data will always be transmitted as JSON to Socrata even though different forms are accepted.
     *
     * @param  array|Converter|JSON $payload  The data that will be upserted to the Socrata dataset as a PHP array, an
     *                                        instance of a Converter child class, or a JSON string
     *
     * @return string A JSON encoded string available to be used for UrlQuery requsts
     */
    private function handleJson ($payload)
    {
        $uploadData = $payload;

        if (is_array($payload))
        {
            $uploadData = json_encode($payload);
        }
        else if ($payload instanceof Converter)
        {
            $uploadData = $payload->toJson();
        }
        else if (!StringUtilities::isJson($payload))
        {
            throw new \InvalidArgumentException("The given data is not valid JSON");
        }

        return $uploadData;
    }

    /**
     * Interact with an individual row. Either to retrieve it or to delete it; both actions use the same API endpoint
     * with the exception of what type of request is sent.
     *
     * @param  string $rowID  The 4x4 resource ID of the dataset to work with
     * @param  string $method Either `get` or `delete`
     *
     * @return mixed
     */
    private function individualRow ($rowID, $method)
    {
        $headers = array();

        // For a single row, the format is the `resourceID/rowID.json`, so we'll use that as the "location" of the Api URL
        $apiEndPoint = $this->buildApiUrl("resource", $this->resourceId . "/" . $rowID);

        $urlQuery = new UrlQuery($apiEndPoint, $this->sodaClient->getToken(), $this->sodaClient->getEmail(), $this->sodaClient->getPassword());
        $urlQuery->setOAuth2Token($this->sodaClient->getOAuth2Token());

        $result = $this->sendIndividualRequest($urlQuery, $method, $this->sodaClient->associativeArrayEnabled(), $headers);

        $this->setApiVersion($headers);

        return $result;
    }

    /**
     * Send the appropriate request header based on the method that's required
     *
     * @param UrlQuery $urlQuery          The object for the API endpoint
     * @param string   $method            Either `get` or `delete`
     * @param bool     $associativeArrays Whether or not to return the information as an associative array
     * @param array    $headers           An array with the cURL headers received
     *
     * @return mixed
     */
    private function sendIndividualRequest ($urlQuery, $method, $associativeArrays, &$headers)
    {
        if ($method === "get")
        {
            return $urlQuery->sendGet("", $associativeArrays, $headers);
        }
        else if ($method === "delete")
        {
            return $urlQuery->sendDelete($associativeArrays, $headers);
        }

        throw new \InvalidArgumentException("Invalid request method");
    }

    /**
     * Determine and save the API version if it does not exist for easy access later
     *
     * @param array $headers An array with the cURL headers received
     */
    private function setApiVersion (array $headers)
    {
        // Only set the API version number if it hasn't been set yet
        if ($this->apiVersion == 0)
        {
            $this->apiVersion = $this->parseApiVersion($headers);
        }
    }

    /**
     * Determine the version number of the API this dataset is using
     *
     * @param  array  $responseHeaders An array with the cURL headers received
     *
     * @return double The Socrata API version number this dataset uses
     */
    private function parseApiVersion (array $responseHeaders)
    {
        // A header that's unique to the legacy API
        if (array_key_exists('X-SODA2-Legacy-Types', $responseHeaders) && $responseHeaders['X-SODA2-Legacy-Types'])
        {
            return 1;
        }

        // A header that's unique to the new API
        if (array_key_exists('X-SODA2-Truth-Last-Modified', $responseHeaders))
        {
            if (empty($this->metadata))
            {
                $this->getMetadata();
            }

            if ($this->metadata['newBackend'])
            {
                return 2.1;
            }

            return 2;
        }

        return 0;
    }
}
