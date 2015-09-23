<?php

namespace allejo\Socrata\Exceptions;

class SodaException extends \Exception
{
    /**
     * Store the JSON object response when a SODA error is thrown
     *
     * @var array
     */
    private $jsonResponse;

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
