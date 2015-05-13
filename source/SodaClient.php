<?php

namespace allejo\Socrata;

class SodaClient
{
    private $url;
    private $token;
    private $email;
    private $password;

    public function __construct ($url, $token = "", $email = "", $password = "")
    {
        $this->url      = $url;
        $this->token    = $token;
        $this->email    = $email;
        $this->password = $password;
    }

    /**
     * [getResource description]
     *
     * @param  string           $resourceID        [description]
     * @param  string|SoqlQuery $filterOrSoqlQuery [description]
     *
     * @return array()                             [description]
     */
    public function getResource($resourceID, $filterOrSoqlQuery)
    {
        if (preg_match('^[a-z0-9]{4}-[a-z0-9]{4}$', $resourceID))
        {
            throw new Exception("An invalid resource ID was given", 1);
        }

        $uq = new UrlQuery($this->url, $this->token);

        return $uq->sendGet($filterOrSoqlQuery);
    }
}