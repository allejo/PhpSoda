<?php

/**
 * This file contains the UrlQuery class which is a wrapper for cURL
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Utilities;

use allejo\Socrata\Exceptions\CurlException;
use allejo\Socrata\Exceptions\HttpException;
use allejo\Socrata\Exceptions\SodaException;

/**
 * A wrapper class for working with cURL requests.
 *
 * This class configures cURL with all of the appropriate authentication information and proper cURL configuration for
 * processing requests.
 *
 * There's no need to access this class outside of this library as the appropriate functionality is properly wrapped in
 * the SodaDataset class.
 *
 * @package allejo\Socrata\Utilities
 * @since   0.1.0
 */
class UrlQuery
{
    /**
     * The default protocol the Soda API expects
     */
    const DEFAULT_PROTOCOL = "https";

    /**
     * The API endpoint that will be used in all requests
     *
     * @var string
     */
    private $url;

    /**
     * The cURL object this class is a wrapper for
     *
     * @var resource
     */
    private $cURL;

    /**
     * The Socrata API token
     *
     * @var string
     */
    private $token;

    /**
     * HTTP headers sent in all requests
     *
     * @var string[]
     */
    private $headers;

    /**
     * The OAuth 2.0 token sent in all requests
     *
     * @var string
     */
    private $oAuth2Token;

    /**
     * Configure all of the authentication needed for cURL requests and the API endpoint
     *
     * **Note** If OAuth 2.0 is used for authentication, do not give values to the $email and $password parameters;
     * instead, use the `setOAuth2Token()` function. An API token will still be required to bypass throttling.
     *
     * @param string $url      The API endpoint this instance will be calling
     * @param string $token    The API token used in order to bypass throttling
     * @param string $email    The email address of the user being authenticated through Basic Authentication
     * @param string $password The password for the user being authenticated through Basic Authentication
     *
     * @see   setOAuth2Token
     *
     * @since 0.1.0
     */
    public function __construct ($url, $token = "", $email = "", $password = "")
    {
        $this->url   = $url;
        $this->token = $token;
        $this->cURL  = curl_init();

        // Build up the headers we'll need to pass
        $this->headers = array(
                             'Accept: application/json',
                             'Content-type: application/json',
                             'X-App-Token: ' . $this->token
                         );

        $this->configureCurl($email, $password);
    }

    /**
     * Clean up after ourselves; clean up the cURL object.
     */
    public function __destruct ()
    {
        curl_close($this->cURL);
    }

    /**
     * Set the OAuth 2.0 token that requests will be using. This function does **not** retrieve a token, it simply uses
     * the existing token and sends it as authentication.
     *
     * @param string $token The OAuth 2.0 token used in requests
     *
     * @since 0.1.2
     */
    public function setOAuth2Token ($token)
    {
        if (!StringUtilities::isNullOrEmpty($token))
        {
            $this->oAuth2Token = $token;
            $this->headers[]   = "Authorization: OAuth " . $this->oAuth2Token;
        }
    }

    /**
     * Send a GET request
     *
     * @param  string $params           The GET parameters to be appended to the API endpoint
     * @param  bool   $associativeArray When true, the returned data will be associative arrays; otherwise, it'll be an
     *                                  StdClass object.
     * @param  array  $headers          An array where the return HTTP headers will be stored
     *
     * @see    SodaClient::enableAssociativeArrays
     *
     * @since  0.1.0
     *
     * @return mixed  An associative array matching the returned JSON result or an StdClass object
     */
    public function sendGet ($params, $associativeArray, &$headers = NULL)
    {
        if (is_array($params))
        {
            $parameters = self::formatParameters($params);
            $full_url   = self::buildQuery($this->url, $parameters);
        }
        else if (!empty($params))
        {
            $full_url = $this->url . "?" . $params;
        }
        else
        {
            $full_url = $this->url;
        }

        curl_setopt($this->cURL, CURLOPT_URL, $full_url);

        return $this->handleQuery($associativeArray, $headers);
    }

    /**
     * Send a POST request
     *
     * @param  string $dataAsJson       The data that will be sent to Socrata as JSON
     * @param  bool   $associativeArray When true, the returned data will be associative arrays; otherwise, it'll be an
     *                                  StdClass object.
     * @param  array  $headers          An array where the return HTTP headers will be stored
     *
     * @see    SodaClient::enableAssociativeArrays
     *
     * @since  0.1.0
     *
     * @return mixed  An associative array matching the returned JSON result or an StdClass object
     */
    public function sendPost ($dataAsJson, $associativeArray, &$headers = NULL)
    {
        $this->setPostFields($dataAsJson);

        curl_setopt_array($this->cURL, array(
            CURLOPT_POST => true,
            CURLOPT_CUSTOMREQUEST => "POST"
        ));

        return $this->handleQuery($associativeArray, $headers);
    }

    /**
     * Send a PUT request
     *
     * @param  string $dataAsJson       The data that will be sent to Socrata as JSON
     * @param  bool   $associativeArray When true, the returned data will be associative arrays; otherwise, it'll be an
     *                                  StdClass object.
     * @param  array  $headers          An array where the return HTTP headers will be stored
     *
     * @see    SodaClient::enableAssociativeArrays
     *
     * @since  0.1.0
     *
     * @return mixed  An associative array matching the returned JSON result or an StdClass object
     */
    public function sendPut ($dataAsJson, $associativeArray, &$headers = NULL)
    {
        $this->setPostFields($dataAsJson);

        curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, "PUT");

        return $this->handleQuery($associativeArray, $headers);
    }

    /**
     * Send a DELETE request
     *
     * @param  bool   $associativeArray When true, the returned data will be associative arrays; otherwise, it'll be an
     *                                  StdClass object.
     * @param  array  $headers          An array where the return HTTP headers will be stored
     *
     * @see    SodaClient::enableAssociativeArrays
     *
     * @since  0.1.2
     *
     * @return mixed  An associative array matching the returned JSON result or an StdClass object
     */
    public function sendDelete ($associativeArray, &$headers = NULL)
    {
        curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, "DELETE");

        $this->handleQuery($associativeArray, $headers, true);
    }

    /**
     * Set the POST fields that will be submitted in the cURL request
     *
     * @param string $dataAsJson The data that will be sent to Socrata as JSON
     *
     * @since 0.1.0
     */
    private function setPostFields ($dataAsJson)
    {
        curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $dataAsJson);
    }

    /**
     * Handle the execution of the cURL request. This function will also save the returned HTTP headers and handle them
     * appropriately.
     *
     * @param  bool  $associativeArray When true, the returned data will be associative arrays; otherwise, it'll be an
     *                                 StdClass object.
     * @param  array $headers          The reference to the array where the returned HTTP headers will be stored
     * @param  bool  $ignoreReturn     True if the returned body should be ignored
     *
     * @since  0.1.0
     *
     * @throws \allejo\Socrata\Exceptions\CurlException If cURL is misconfigured or encounters an error
     * @throws \allejo\Socrata\Exceptions\HttpException An HTTP status of something other 200 is returned
     * @throws \allejo\Socrata\Exceptions\SodaException A SODA API error is returned
     *
     * @return mixed|NULL
     */
    private function handleQuery ($associativeArray, &$headers, $ignoreReturn = false)
    {
        $result = $this->executeCurl();

        // Ignore "100 Continue" headers
        $continueHeader = "HTTP/1.1 100 Continue\r\n\r\n";

        if (strpos($result, $continueHeader) === 0)
        {
            $result = str_replace($continueHeader, '', $result);
        }

        list($header, $body) = explode("\r\n\r\n", $result, 2);

        $this->saveHeaders($header, $headers);

        if ($ignoreReturn)
        {
            return NULL;
        }

        $resultArray = $this->handleResponseBody($body, $result);

        return ($associativeArray) ? $resultArray : json_decode($body, false);
    }

    /**
     * Configure the cURL instance and its credentials for Basic Authentication that this instance will be working with
     *
     * @param string $email    The email for the user with Basic Authentication
     * @param string $password The password for the user with Basic Authentication
     *
     * @since 0.1.0
     */
    private function configureCurl ($email, $password)
    {
        curl_setopt_array($this->cURL, array(
            CURLOPT_URL => $this->url,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSLVERSION => 6
        ));

        if (!StringUtilities::isNullOrEmpty($email) && !StringUtilities::isNullOrEmpty($password))
        {
            curl_setopt_array($this->cURL, array(
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $email . ":" . $password
            ));
        }
    }

    /**
     * Execute the finalized cURL object that has already been configured
     *
     * @since  0.1.0
     *
     * @throws \allejo\Socrata\Exceptions\CurlException If cURL is misconfigured or encounters an error
     *
     * @return mixed
     */
    private function executeCurl ()
    {
        $result = curl_exec($this->cURL);

        if (!$result)
        {
            throw new CurlException($this->cURL);
        }

        return $result;
    }

    /**
     * Check for unexpected errors or SODA API errors
     *
     * @param  string $body   The body of the response
     * @param  string $result The unfiltered result cURL received
     *
     * @since  0.1.0
     *
     * @throws \allejo\Socrata\Exceptions\HttpException If the $body returned was not a JSON object
     * @throws \allejo\Socrata\Exceptions\SodaException The returned JSON object in the $body was a SODA API error
     *
     * @return mixed An associative array of the decoded JSON response
     */
    private function handleResponseBody ($body, $result)
    {
        // We somehow got a server error from Socrata without a JSON object with details
        if (!StringUtilities::isJson($body))
        {
            $httpCode = curl_getinfo($this->cURL, CURLINFO_HTTP_CODE);

            throw new HttpException($httpCode, $result);
        }

        $resultArray = json_decode($body, true);

        // We got an error JSON object back from Socrata
        if (array_key_exists('error', $resultArray) && $resultArray['error'])
        {
            throw new SodaException($resultArray);
        }

        return $resultArray;
    }

    /**
     * Handle the returned HTTP headers and save them into an array
     *
     * @param string $header  The returned HTTP headers
     * @param array  $headers The reference to the array where our headers will be saved
     *
     * @since 0.1.0
     */
    private function saveHeaders ($header, &$headers)
    {
        if ($headers === NULL)
        {
            return;
        }

        $header       = explode("\r\n", $header);
        $headers      = array();
        $headerLength = count($header);

        // The 1st element is the HTTP code, so we can safely skip it
        for ($i = 1; $i < $headerLength; $i++)
        {
            list($key, $val) = explode(":", $header[$i]);
            $headers[$key] = trim($val);
        }
    }

    /**
     * Build a URL with GET parameters formatted into the URL
     *
     * @param string  $url    The base URL
     * @param array   $params The GET parameters that need to be appended to the base URL
     *
     * @since 0.1.0
     *
     * @return string A URL with GET parameters
     */
    private static function buildQuery ($url, $params = array())
    {
        $full_url = $url;

        if (count($params) > 0)
        {
            $full_url .= "?" . implode("&", $params);
        }

        return $full_url;
    }

    /**
     * Format an array into a URL encoded values to be submitted in cURL requests
     *
     * **Input**
     *
     * ```php
     * array(
     *     "foo"   => "bar",
     *     "param" => "value"
     * )
     * ```
     *
     * **Output**
     *
     * ```php
     * array(
     *     "foo=bar",
     *     "param=value"
     * )
     * ```
     *
     * @param  array    $params An array containing parameter names as keys and parameter values as values in the array.
     *
     * @return string[]         A URL encoded and combined array of GET parameters to be sent
     */
    private static function formatParameters ($params)
    {
        $parameters = array();

        foreach ($params as $key => $value)
        {
            $parameters[] = rawurlencode($key) . "=" . rawurlencode($value);
        }

        return $parameters;
    }
}
