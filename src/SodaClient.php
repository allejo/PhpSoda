<?php

/**
 * This file contains the content of the SodaClient object
 *
 * @copyright 2015 Vladimir Jimenez
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache 2.0
 */

namespace allejo\Socrata;

use allejo\Socrata\Exceptions\InvalidResourceException;
use allejo\Socrata\Utilities\UrlQuery;

/**
 * A client object to interact with the Socrata API
 *
 * @package allejo\Socrata
 */
class SodaClient
{
    /**
     * The URL or domain of where the data set will be received from
     *
     * @var string
     */
    private $domain;

    /**
     * The AppToken used to read private data and to allow the application to work with less throttling
     *
     * @var string
     */
    private $token;

    /**
     * The user email for the account that will be adding, deleting, or modifying data
     *
     * @var string
     */
    private $email;

    /**
     * The password for said account
     *
     * @var string
     */
    private $password;

    /**
     * Whether or not to return the decoded JSON as an associative array. When set to false, it will return stdClass
     * objects
     *
     * @var bool
     */
    private $associativeArray;

    /**
     * Create a client object to connect to the Socrata API
     *
     * @param string $url      The URL or domain of the Socrata data set
     * @param string $token    The AppToken used to access this information
     * @param string $email    Username for authentication
     * @param string $password Password for authentication
     */
    public function __construct ($url, $token = "", $email = "", $password = "")
    {
        $this->domain   = rtrim(preg_replace('/http(s)?:\/\//', "", $url), '/');
        $this->token    = $token;
        $this->email    = $email;
        $this->password = $password;
        $this->associativeArray = true;
    }

    /**
     * When fetching a data set, the returned data will be in an array of associative arrays.
     *
     * ```
     * Array
     * (
     *     [foo] => Test data
     *     [bar] => Array
     *     (
     *         [baaz] => Testing
     *         [fooz] => Array
     *         (
     *             [baz] => Testing again
     *         )
     *
     *     )
     *     [foox] => Just test
     * )
     * ```
     *
     * When returned in this format, all of the children elements are objects and are accessed as such.
     *
     *     $myVariable = $results['bar']['baz']; // Testing
     */
    public function enableAssociativeArrays()
    {
        $this->associativeArray = true;
    }

    /**
     * When fetching a data set, the returned data will be in an array of stdClass objects. When AssociativeArrays is
     * disabled, the returned data will in the follow format:
     *
     * ```
     * stdClass Object
     * (
     *     [foo] => Test data
     *     [bar] => stdClass Object
     *     (
     *         [baz] => Testing
     *         [foz] => stdClass Object
     *         (
     *             [baz] => Testing again
     *         )
     *     )
     *     [fox] => Just test
     * )
     * ```
     *
     * When returned in this format, all of the children elements are objects and are accessed as such.
     *
     *     $myVariable = $results->bar->baz; // Testing
     *
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
     * @see    enableAssociativeArrays()
     * @see    disableAssociativeArrays()
     *
     * @throws InvalidResourceException If the resource ID isn't in the format of xxxx-xxxx
     *
     * @return array The data set as a PHP array. The array will contain associative arrays or stdClass objects from
     *               the decoded JSON received from the data set.
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

    /**
     * Build the URL that will be used to access the API
     *
     * @param  string $resourceId The 4x4 resource ID of a data set
     *
     * @return string The API URL
     */
    private function buildResourceUrl($resourceId)
    {
        return sprintf("%s://%s/resource/%s.json", UrlQuery::DefaultProtocol, $this->domain, $resourceId);
    }
}