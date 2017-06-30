<?php

/**
 * This file contains the SodaClient class
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata;

use GuzzleHttp\Client;

/**
 * An object provided to handle tokens, authentication, and configuration for interacting with the the Socrata API.
 *
 * @api
 * @since 0.1.0
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
     * The AppToken used to allow the application to work with less throttling
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
     * The Guzzle client used for making URL calls
     *
     * @var Client
     */
    private $client;

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
     * @api
     *
     * @param string $domain   The URL or domain of the Socrata data set
     * @param string $token    The AppToken used to access this information
     * @param string $email    Username for authentication
     * @param string $password Password for authentication
     *
     * @since 0.1.0
     */
    public function __construct ($domain, $token = "", $email = "", $password = "")
    {
        $this->domain           = rtrim(preg_replace('/http(s)?:\/\//', "", $domain), '/');
        $this->token            = $token;
        $this->email            = $email;
        $this->password         = $password;
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
     * When returned in this format, all of the children elements are array elements and are accessed as such.
     *
     * ```php
     *     $myVariable = $results['bar']['baz']; // Testing
     * ```
     *
     * @since 0.1.0
     */
    public function enableAssociativeArrays ()
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
     * ```php
     *     $myVariable = $results->bar->baz; // Testing
     * ```
     *
     * @since 0.1.0
     */
    public function disableAssociativeArrays ()
    {
        $this->associativeArray = false;
    }

    /**
     * Get whether or not the returned data should be associative arrays or as stdClass objects
     *
     * @since 0.1.0
     *
     * @return bool True if the data is returned as associative arrays
     */
    public function associativeArrayEnabled ()
    {
        return $this->associativeArray;
    }

    /**
     * Get the domain of the API endpoint. This function will **always** return just the domain without the protocol
     * in order to let this library use the appropriate protocol
     *
     * @since 0.1.0
     *
     * @return string The domain of the API endpoint
     */
    public function getDomain ()
    {
        return $this->domain;
    }

    /**
     * Get the email of the account that will be used for authenticated actions
     *
     * @since 0.1.0
     *
     * @return string The user's email address. Returns an empty string if not set.
     */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
     * Get the app token used by the library to bypass throttling and appear as a registered application
     *
     * @since 0.1.0
     *
     * @return string The app token used. Returns an empty string if not set.
     */
    public function getToken ()
    {
        return $this->token;
    }

    /**
     * Get the password of the account that will be used for authenticated actions
     *
     * @since 0.1.0
     *
     * @return string The password used. Returns an empty string if not set.
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     * Get the Guzzle client we'll be using for URL calls.
     *
     * @api
     *
     * @since  2.0.0
     *
     * @return Client
     */
    public function getGuzzleClient()
    {
        if ($this->client === null)
        {
            $guzzleConf = [
                'base_uri' => 'https://' . $this->getDomain(),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ]
            ];

            if (!empty($this->getEmail()) && !empty($this->getPassword()))
            {
                $guzzleConf['auth'] = [$this->getEmail(), $this->getPassword()];
            }
            elseif (empty($this->getEmail()) xor empty($this->getPassword()))
            {
                trigger_error('Either an email or password is missing; HTTP authentication will be disabled for requests.', E_USER_WARNING);
            }

            $this->client = new Client($guzzleConf);
        }

        return $this->client;
    }

    /**
     * Define your own Guzzle client with your own configuration.
     *
     * @api
     *
     * @param Client $client
     *
     * @since 2.0.0
     */
    public function setGuzzleClient(Client $client)
    {
        $this->client = $client;
    }
}
