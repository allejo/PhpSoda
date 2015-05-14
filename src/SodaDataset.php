<?php

namespace allejo\Socrata;

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
        $this->urlQuery   = $this->createUrlQuery();
    }

    /**
     * Fetch a data set based on a resource ID
     *
     * @param  string|SoqlQuery $filterOrSoqlQuery A simple filter or a SoqlQuery to filter the results
     *
     * @see    SodaClient::enableAssociativeArrays()
     * @see    SodaClient::disableAssociativeArrays()
     *
     * @return array The data set as a PHP array. The array will contain associative arrays or stdClass objects from
     *               the decoded JSON received from the data set.
     */
    public function getDataset ($filterOrSoqlQuery = "")
    {
        return $this->urlQuery->sendGet($filterOrSoqlQuery, $this->sodaClient->associativeArrayEnabled());
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function upsert($data)
    {
        $upsertData = $data;

        if (is_array($data))
        {
            $upsertData = json_encode($data);
        }
        else if (!StringUtilities::isJson($data))
        {
            throw new \InvalidArgumentException("The given data is not valid JSON");
        }

        return $this->urlQuery->sendPost($upsertData, $this->sodaClient->associativeArrayEnabled());
    }

    /**
     * Create a UrlQuery object and set up authentication
     *
     * @return UrlQuery The object used to make a request
     */
    private function createUrlQuery()
    {
        $uq = new UrlQuery($this->buildResourceUrl($this->resourceId), $this->sodaClient->getToken());

        if ($this->sodaClient->getEmail() != null && $this->sodaClient->getPassword() != null)
        {
            $uq->setAuthentication($this->sodaClient->getEmail(), $this->sodaClient->getPassword());
        }

        return $uq;
    }

    /**
     * Build the URL that will be used to access the API
     *
     * @param  string $resourceId The 4x4 resource ID of a data set
     *
     * @return string The API URL
     */
    private function buildResourceUrl($resourceId)
    {
        return sprintf("%s://%s/resource/%s.json", UrlQuery::DefaultProtocol, $this->sodaClient->getDomain(), $resourceId);
    }
}