<?php

/**
 * This file contains the content of the SodaClient object
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 */

namespace allejo\Socrata;

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

    public function associativeArrayEnabled()
    {
        return $this->associativeArray;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getPassword()
    {
        return $this->password;
    }
}