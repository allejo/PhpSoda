<?php

/**
 * This file contains the SodaException
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Exceptions;

use GuzzleHttp\Exception\ClientException;

/**
 * An exception thrown if a SODA API error is encountered.
 *
 * A SODA API error is in the form of a JSON object with a boolean named 'error' set to true.
 *
 * @since 2.0.0 Extends \RuntimeException
 * @since 0.1.0
 */
class SodaException extends \RuntimeException
{
    /**
     * The JSON object response when a SODA error is thrown
     *
     * @var array
     */
    private $jsonResponse;

    /**
     * Create an exception
     *
     * @param array      $jsonResponse The JSON object returned by Socrata with error information
     * @param \Throwable $previous     The previous exception; typically the exception that was cast into this
     *
     * @since 0.1.0
     */
    public function __construct ($jsonResponse, \Throwable $previous = null)
    {
        $this->jsonResponse = $jsonResponse;

        parent::__construct($this->jsonResponse['message'], 0, $previous);

        $this->code = (isset($this->jsonResponse['code'])) ? $this->jsonResponse['code'] : 'error.unknown';
    }

    /**
     * Get an associative array of the error that was thrown by Socrata
     *
     * @since 0.1.2
     *
     * @return array An associative array of the error Socrata threw
     */
    public function getJsonResponse ()
    {
        return $this->jsonResponse;
    }

    /**
     * Cast a Guzzle ClientException into a SodaException
     *
     * @api
     *
     * @param  ClientException $e
     *
     * @since  2.0.0
     *
     * @return SodaException
     */
    public static function cast(ClientException $e)
    {
        $json = json_decode($e->getResponse()->getBody()->getContents(), true);

        return (new self($json, $e));
    }
}
