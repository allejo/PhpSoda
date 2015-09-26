<?php

/**
 * This file contains the SodaException
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Exceptions;

/**
 * An exception thrown if a SODA API error is encountered.
 *
 * A SODA API error is in the form of a JSON object with a boolean named 'error' set to true.
 *
 * @package allejo\Socrata\Exceptions
 * @since   0.1.0
 */
class SodaException extends \Exception
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
     * @param array $jsonResponse The JSON object returned by Socrata with error information
     *
     * @since 0.1.0
     */
    public function __construct ($jsonResponse)
    {
        $this->jsonResponse = $jsonResponse;

        $this->code    = (isset($this->jsonResponse['code'])) ? $this->jsonResponse['code'] : 'error.unknown';
        $this->message = $this->jsonResponse['message'];
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
}
