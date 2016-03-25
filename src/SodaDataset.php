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

/**
 * An object provided to interact with a Socrata dataset directly. Provides functionality for fetching the dataset, an
 * individual row, or updating/replacing a dataset.
 *
 * @package allejo\Socrata
 * @since   0.1.0
 */
class SodaDataset
{
    /**
     * The client with all the authentication and configuration set
     *
     * @var SodaClient
     */
    private $sodaClient;

    /**
     * The object used to make URL jobs for common requests
     *
     * @var UrlQuery
     */
    private $urlQuery;

    /**
     * The 4x4 resource ID of a dataset
     *
     * @var string
     */
    private $resourceId;

    /**
     * The API version of the dataset being worked with
     *
     * @var int
     */
    private $apiVersion;

    /**
     * The API's cached metadata
     *
     * @var array
     */
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

        $this->urlQuery->setOAuth2Token($this->sodaClient->getOAuth2Token());
    }

    /**
     * Get the API version this dataset is using
     *
     * @since  0.1.0
     *
     * @return double The API version number
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
     * @param bool $forceFetch Set to true if the cached metadata for the dataset is outdata or needs to be refreshed
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     *
     * @since  0.1.0
     *
     * @return array The metadata as a PHP array. The array will contain associative arrays or stdClass objects from
     *               the decoded JSON received from the data set.
     */
    public function getMetadata ($forceFetch = false)
    {
        if (empty($this->metadata) || $forceFetch)
        {
            $metadataUrlQuery = new UrlQuery($this->buildViewUrl(), $this->sodaClient->getToken(), $this->sodaClient->getEmail(), $this->sodaClient->getPassword());
            $metadataUrlQuery->setOAuth2Token($this->sodaClient->getOAuth2Token());

            $this->metadata = $metadataUrlQuery->sendGet("", $this->sodaClient->associativeArrayEnabled());
        }

        return $this->metadata;
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

        $this->setApiVersion($headers);

        return $dataset;
    }

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
     * Fetch an individual row from a dataset.
     *
     * @param  int|string $rowID The row identifier of the row to fetch; if no identifier is set for the dataset, the
     *                           internal row identifier should be used
     *
     * @link   http://dev.socrata.com/publishers/direct-row-manipulation.html#retrieving-an-individual-row  Retrieving
     *         An Individual Row
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     *
     * @since  0.1.2
     *
     * @return array The data set as a PHP array. The array will contain associative arrays or stdClass objects from
     *               the decoded JSON received from the data set.
     */
    public function getRow ($rowID)
    {
        return $this->individualRow($rowID, "get");
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
    private function setApiVersion ($headers)
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
    private function parseApiVersion ($responseHeaders)
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
