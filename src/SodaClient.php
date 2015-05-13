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

    public function __construct ($url, $token = "", $email = "", $password = "")
    {
        $this->domain   = rtrim(preg_replace('/http(s)?:\/\//', "", $url), '/');
        $this->token    = $token;
        $this->email    = $email;
        $this->password = $password;
    }

    /**
     * Fetch a data set based on a resource ID
     *
     * @param  string           $resourceID        The 4x4 resource ID of a dataset
     * @param  string|SoqlQuery $filterOrSoqlQuery A simple filter or a SoqlQuery to filter the results
     *
     * @throws InvalidResourceException
     *
     * @return array A JSON decoded array containing the information returned from Socrata
     */
    public function getResource($resourceID, $filterOrSoqlQuery = "")
    {
        if (!preg_match('/^[a-z0-9]{4}-[a-z0-9]{4}$/', $resourceID))
        {
            throw new InvalidResourceException("The resource ID given didn't fit the expected criteria");
        }

        $uq = new UrlQuery($this->buildResourceUrl($resourceID), $this->token);

        if (isset($this->email) && isset($this->password))
        {
            $uq->setAuthentication($this->email, $this->password);
        }

        return $uq->sendGet($filterOrSoqlQuery);
    }

    private function buildResourceUrl($resourceId)
    {
        return sprintf("%s://%s/resource/%s.json", UrlQuery::DefaultProtocol, $this->domain, $resourceId);
    }
}