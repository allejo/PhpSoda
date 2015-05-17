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

    public function __construct ($sodaClient, $resourceID)
    {
        StringUtilities::validateResourceID($resourceID);

        if (!($sodaClient instanceof SodaClient))
        {
            throw new \InvalidArgumentException("The first variable is expected to be a SodaClient object");
        }

        $this->sodaClient = $sodaClient;
        $this->resourceId = $resourceID;
        $this->urlQuery   = new UrlQuery($this->buildResourceUrl(), $this->sodaClient->getToken(), $this->sodaClient->getEmail(), $this->sodaClient->getPassword());
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
        if (!($filterOrSoqlQuery instanceof SoqlQuery) && StringUtilities::isNullOrEmpty($filterOrSoqlQuery))
        {
            $filterOrSoqlQuery = new SoqlQuery();
        }

        return $this->urlQuery->sendGet($filterOrSoqlQuery, $this->sodaClient->associativeArrayEnabled());
    }

    /**
     * @param $data
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
}
