<?php

namespace allejo\Socrata;

use allejo\Socrata\Exceptions\InvalidResourceException;
use allejo\Socrata\Utilities\UrlQuery;

class SodaClient
{
    private $domain;
    private $token;
    private $email;
    private $password;
    private $associativeArray;

    public function __construct ($url, $token = "", $email = "", $password = "")
    {
        $this->domain   = rtrim(preg_replace('/http(s)?:\/\//', "", $url), '/');
        $this->token    = $token;
        $this->email    = $email;
        $this->password = $password;
        $this->associativeArray = true;
    }

    /**
     * When fetching a data set, the returned data will be in an array of associative arrays
     */
    public function enableAssociativeArrays()
    {
        $this->associativeArray = true;
    }

    /**
     * When fetching a data set, the returned data will be in an array of stdClass objects
     */
    public function disableAssociativeArrays()
    {
        $this->associativeArray = false;
    }

    /**
     * Fetch a data set based on a resource ID
     *
     * @param  string           $resourceID        The 4x4 resource ID of a data set
     * @param  string|SoqlQuery $filterOrSoqlQuery A simple filter or a SoqlQuery to filter the results
     *
     * @throws \allejo\Socrata\Exceptions\InvalidResourceException
     *
     * @return array
     */
    public function getResource($resourceID, $filterOrSoqlQuery = "")
    {
        if (!preg_match('/^[a-z0-9]{4}-[a-z0-9]{4}$/', $resourceID))
        {
            throw new InvalidResourceException("The resource ID given didn't fit the expected criteria");
        }

        $uq = new UrlQuery($this->buildResourceUrl($resourceID), $this->token);

        if (!empty($this->email) && !empty($this->password))
        {
            $uq->setAuthentication($this->email, $this->password);
        }

        return $uq->sendGet($filterOrSoqlQuery, $this->associativeArray);
    }

    private function buildResourceUrl($resourceId)
    {
        return sprintf("%s://%s/resource/%s.json", UrlQuery::DefaultProtocol, $this->domain, $resourceId);
    }
}