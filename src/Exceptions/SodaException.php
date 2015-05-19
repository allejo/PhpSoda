<?php

namespace allejo\Socrata\Exceptions;

class SodaException extends \Exception
{
    public function __construct ($jsonResponse)
    {
        $this->code    = (isset($jsonResponse['code'])) ? $jsonResponse['code'] : 'error.unknown';
        $this->message = $jsonResponse['message'];
    }
}
